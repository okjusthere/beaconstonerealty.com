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

/**
 * Returns the vertical object-position percentage for each advisor's photo.
 * Lower % = show more of the top (face near top of image).
 * Higher % = show more of the bottom (face lower in image).
 * Tune per person so all faces align at a consistent vertical focal point.
 */
function getFacePosition(title: string): string {
  switch (title.trim()) {
    case 'Ziwei (Audrey) Wen':
      return '18%';
    case 'Qiao Chen':
      return '28%';
    case 'Tatyana Ilieva':
      return '20%';
    case 'Juliana Gamboa':
      return '0%';
    case 'Nick Yu':
      return '20%';
    case 'Xiangyu (Allen) Zhang':
      return '18%';
    default:
      return '20%';
  }
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
  const perPage = 2;
  const count = advisors.length;

  // Clone last `perPage` items before and first `perPage` items after for seamless looping
  const clonedBefore = advisors.slice(-perPage);
  const clonedAfter = advisors.slice(0, perPage);
  const extendedSlides = [...clonedBefore, ...advisors, ...clonedAfter];

  // offset is in the "real" range: starts at perPage (first real item)
  const [offset, setOffset] = useState(perPage);
  const [isTransitioning, setIsTransitioning] = useState(true);
  const trackRef = useRef<HTMLDivElement>(null);
  const timerRef = useRef<ReturnType<typeof setInterval> | null>(null);

  const slideWidth = 100 / perPage; // percentage per slide

  const next = useCallback(() => {
    setIsTransitioning(true);
    setOffset((prev) => prev + 1);
  }, []);

  const prev = useCallback(() => {
    setIsTransitioning(true);
    setOffset((prev) => prev - 1);
  }, []);

  // When transition ends, check if we need to jump to the "real" position
  const handleTransitionEnd = useCallback(() => {
    if (offset >= perPage + count) {
      // Scrolled past the last real item into cloned-after zone
      setIsTransitioning(false);
      setOffset(perPage + (offset - perPage - count));
    } else if (offset < perPage) {
      // Scrolled before the first real item into cloned-before zone
      setIsTransitioning(false);
      setOffset(count + offset);
    }
  }, [offset, count, perPage]);

  // After a no-transition jump, re-enable transition on next frame
  useEffect(() => {
    if (!isTransitioning) {
      const frame = requestAnimationFrame(() => {
        setIsTransitioning(true);
      });
      return () => cancelAnimationFrame(frame);
    }
  }, [isTransitioning]);

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
          ref={trackRef}
          className={styles.carouselTrack}
          style={{
            transform: `translateX(-${offset * slideWidth}%)`,
            transition: isTransitioning ? 'transform 0.5s ease' : 'none',
          }}
          onTransitionEnd={handleTransitionEnd}
        >
          {extendedSlides.map((advisor, i) => (
            <div key={`${advisor.id}-${i}`} className={styles.carouselSlide}>
              <Link
                href={advisor.url || '#'}
                className={styles.carouselCard}
                style={{ ['--advisor-face-position' as string]: getFacePosition(advisor.title) }}
              >
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
