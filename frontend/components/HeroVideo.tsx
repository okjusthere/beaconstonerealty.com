'use client';

import { useEffect, useRef, useState } from 'react';

interface HeroVideoProps {
  className?: string;
  embedUrl: string;
  poster: string;
  title: string;
}

export default function HeroVideo({ className, embedUrl, poster, title }: HeroVideoProps) {
  const frameRef = useRef<HTMLDivElement | null>(null);
  const [shouldLoad, setShouldLoad] = useState(false);
  const [isReady, setIsReady] = useState(false);

  useEffect(() => {
    const frame = frameRef.current;
    if (!frame) {
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

    observer.observe(frame);

    return () => observer.disconnect();
  }, []);

  return (
    <div
      ref={frameRef}
      className={className}
      style={{ backgroundImage: `url(${poster})` }}
      aria-hidden="true"
    >
      {shouldLoad ? (
        <iframe
          src={embedUrl}
          title={title}
          allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
          allowFullScreen
          loading="lazy"
          onLoad={() => setIsReady(true)}
          style={{ opacity: isReady ? 1 : 0 }}
        />
      ) : null}
    </div>
  );
}
