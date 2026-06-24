'use client';

import { useState } from 'react';
import styles from './PropertyGallery.module.css';

interface PropertyGalleryProps {
  images: string[];
  title: string;
}

export default function PropertyGallery({ images, title }: PropertyGalleryProps) {
  const [activePhoto, setActivePhoto] = useState(0);
  const activeImage = images[activePhoto] || images[0];

  if (!activeImage) {
    return null;
  }

  return (
    <section className={styles.gallery}>
      <div className={styles.galleryMain}>
        <img
          src={activeImage}
          alt={title}
          className={styles.galleryImage}
        />
      </div>
      {images.length > 1 && (
        <div className={styles.galleryThumbs}>
          {images.map((photo, index) => (
            <button
              key={photo}
              type="button"
              className={`${styles.thumbBtn} ${index === activePhoto ? styles.thumbActive : ''}`}
              onClick={() => setActivePhoto(index)}
            >
              <img src={photo} alt={`${title} photo ${index + 1}`} />
            </button>
          ))}
        </div>
      )}
    </section>
  );
}
