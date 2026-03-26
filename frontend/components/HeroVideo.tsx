'use client';

import MuxPlayer from '@mux/mux-player-react';
import type { CSSProperties } from 'react';
import { useEffect, useRef, useState } from 'react';

interface HeroVideoProps {
  className?: string;
  playbackId: string;
  poster: string;
  title: string;
  muted?: boolean;
}

const SILENT_STYLE = {
  '--top-controls': 'none',
  '--bottom-controls': 'none',
  '--controls-backdrop-color': 'transparent',
} as CSSProperties;

const AUDIBLE_STYLE = {} as CSSProperties;

export default function HeroVideo({
  className,
  playbackId,
  poster,
  title,
  muted = false,
}: HeroVideoProps) {
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
        <MuxPlayer
          playbackId={playbackId}
          title={title}
          videoTitle={title}
          poster={poster}
          autoPlay={muted ? 'muted' : false}
          muted={muted}
          loop={muted}
          playsInline
          preload="auto"
          onCanPlay={() => setIsReady(true)}
          style={{ ...(muted ? SILENT_STYLE : AUDIBLE_STYLE), opacity: isReady ? 1 : 0 }}
        />
      ) : null}
    </div>
  );
}
