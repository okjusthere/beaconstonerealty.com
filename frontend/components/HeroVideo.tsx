'use client';

import { useEffect, useRef, useState } from 'react';

type HeroVideoSource = {
  src: string;
  type: string;
  media?: string;
};

interface HeroVideoProps {
  className?: string;
  poster: string;
  sources: HeroVideoSource[];
}

export default function HeroVideo({ className, poster, sources }: HeroVideoProps) {
  const videoRef = useRef<HTMLVideoElement | null>(null);
  const [shouldLoad, setShouldLoad] = useState(false);

  useEffect(() => {
    const video = videoRef.current;
    if (!video) {
      return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    if (reducedMotion.matches) {
      return;
    }

    const activate = () => setShouldLoad(true);

    if (!('IntersectionObserver' in window)) {
      activate();
      return;
    }

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries.some((entry) => entry.isIntersecting)) {
          activate();
          observer.disconnect();
        }
      },
      { rootMargin: '240px 0px' },
    );

    observer.observe(video);

    return () => observer.disconnect();
  }, []);

  useEffect(() => {
    const video = videoRef.current;
    if (!video || !shouldLoad) {
      return;
    }

    const playAttempt = video.play();
    if (playAttempt) {
      playAttempt.catch(() => {});
    }
  }, [shouldLoad]);

  return (
    <video
      ref={videoRef}
      className={className}
      poster={poster}
      autoPlay={shouldLoad}
      muted
      loop
      playsInline
      preload={shouldLoad ? 'metadata' : 'none'}
      aria-hidden="true"
    >
      {shouldLoad
        ? sources.map((source) => (
            <source
              key={`${source.src}-${source.media || 'default'}`}
              src={source.src}
              type={source.type}
              media={source.media}
            />
          ))
        : null}
    </video>
  );
}
