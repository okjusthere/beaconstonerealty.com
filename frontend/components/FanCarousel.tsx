'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import styles from './FanCarousel.module.css';

interface FanItem {
  id: number;
  thumbnail: string;
  title: string;
}

export default function FanCarousel({ items }: { items: FanItem[] }) {
  const [center, setCenter] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const count = items.length;

  const idx = (i: number) => ((i % count) + count) % count;

  const next = useCallback(() => {
    setCenter((prev) => idx(prev + 1));
  }, [count]);

  const prev = useCallback(() => {
    setCenter((prev) => idx(prev - 1));
  }, [count]);

  const resetTimer = useCallback(() => {
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(next, 4000);
  }, [next]);

  useEffect(() => {
    if (count < 3) return;
    resetTimer();
    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [resetTimer, count]);

  if (count < 3) return null;

  const leftIdx = idx(center - 1);
  const rightIdx = idx(center + 1);

  // Determine CSS class for each item based on position relative to center
  function getPositionClass(itemIndex: number): string {
    if (itemIndex === center) return styles.fanCenter;
    if (itemIndex === leftIdx) return styles.fanLeft;
    if (itemIndex === rightIdx) return styles.fanRight;
    return styles.fanHidden;
  }

  return (
    <div className={styles.fanWrap}>
      <button
        type="button"
        className={`${styles.fanBtn} ${styles.fanBtnPrev}`}
        onClick={() => { prev(); resetTimer(); }}
        aria-label="Previous"
      >
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
          <path d="M15 18l-6-6 6-6" />
        </svg>
      </button>

      <div className={styles.fanStage}>
        {items.map((item, i) => (
          <div key={item.id} className={`${styles.fanCard} ${getPositionClass(i)}`}>
            <img src={item.thumbnail} alt={item.title} />
          </div>
        ))}
      </div>

      <button
        type="button"
        className={`${styles.fanBtn} ${styles.fanBtnNext}`}
        onClick={() => { next(); resetTimer(); }}
        aria-label="Next"
      >
        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
          <path d="M9 18l6-6-6-6" />
        </svg>
      </button>
    </div>
  );
}
