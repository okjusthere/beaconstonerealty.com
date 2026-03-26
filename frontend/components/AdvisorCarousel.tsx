'use client';

import { useCallback, useEffect, useRef, useState } from 'react';
import Link from 'next/link';
import styles from '../app/about/page.module.css';

interface Advisor {
  id: number;
  title: string;
  url: string;
  thumbnail: string;
  description?: string;
  ctaLabel?: string;
}

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

function ChevronLeft() {
  return (
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M15 18l-6-6 6-6" />
    </svg>
  );
}

function ChevronRight() {
  return (
    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M9 18l6-6-6-6" />
    </svg>
  );
}

export default function AdvisorCarousel({ advisors }: { advisors: Advisor[] }) {
  const [offset, setOffset] = useState(0);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);
  const perPage = 2;
  const maxOffset = Math.max(0, advisors.length - perPage);

  const next = useCallback(() => {
    setOffset((prev) => (prev >= maxOffset ? 0 : prev + 1));
  }, [maxOffset]);

  const prev = useCallback(() => {
    setOffset((prev) => (prev <= 0 ? maxOffset : prev - 1));
  }, [maxOffset]);

  const resetTimer = useCallback(() => {
    if (timerRef.current) clearInterval(timerRef.current);
    timerRef.current = setInterval(next, 4000);
  }, [next]);

  useEffect(() => {
    resetTimer();
    return () => {
      if (timerRef.current) clearInterval(timerRef.current);
    };
  }, [resetTimer]);

  function handlePrev() {
    prev();
    resetTimer();
  }

  function handleNext() {
    next();
    resetTimer();
  }

  return (
    <div className={styles.carouselWrap}>
      <button
        type="button"
        className={`${styles.carouselBtn} ${styles.carouselBtnPrev}`}
        onClick={handlePrev}
        aria-label="Previous advisors"
      >
        <ChevronLeft />
      </button>

      <div className={styles.carouselViewport}>
        <div
          className={styles.carouselTrack}
          style={{
            transform: `translateX(-${offset * (100 / perPage)}%)`,
          }}
        >
          {advisors.map((advisor) => (
            <div key={advisor.id} className={styles.carouselSlide}>
              <Link href={advisor.url || '#'} className={styles.carouselCard}>
                {advisor.thumbnail && (
                  <div className={styles.carouselImage}>
                    <img src={advisor.thumbnail} alt={advisor.title} />
                  </div>
                )}
                <div className={styles.carouselBody}>
                  <h3>{advisor.title}</h3>
                  {advisor.description && <p className={styles.carouselRole}>{advisor.description}</p>}
                  <span className={styles.carouselAction}>
                    MEET THE TEAM <ArrowRight />
                  </span>
                </div>
              </Link>
            </div>
          ))}
        </div>
      </div>

      <button
        type="button"
        className={`${styles.carouselBtn} ${styles.carouselBtnNext}`}
        onClick={handleNext}
        aria-label="Next advisors"
      >
        <ChevronRight />
      </button>
    </div>
  );
}
