'use client';

import { useState, useEffect } from 'react';
import styles from './page.module.css';
import Link from 'next/link';

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
}

interface BrokerData {
  title: string;
  description: string;
  content: string;
  thumbnail: string;
  keywords: string;
  field?: Record<string, string>;
}

export default function BrokerDetailPage() {
  const [broker, setBroker] = useState<BrokerData | null>(null);
  const [formData, setFormData] = useState({
    contacts: '', lastname: '', email: '', phone: '', message: '',
  });
  const [submitted, setSubmitted] = useState(false);

  useEffect(() => {
    const params = new URLSearchParams(window.location.search);
    const id = params.get('id');
    if (!id) return;

    const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || 'https://beaconstonerealty.com';
    fetch(`${apiBase}/application/index/news_detail.php?id=${id}`)
      .then(r => r.json())
      .then(data => setBroker(data))
      .catch(() => {});
  }, []);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || '';
      await fetch(`${apiBase}/application/index/inner_message.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(formData).toString(),
      });
      setSubmitted(true);
    } catch { alert('Failed to send.'); }
  };

  if (!broker) {
    return (
      <div className={styles.loading}>
        <div className={styles.spinner} />
        <p>Loading agent profile...</p>
      </div>
    );
  }

  return (
    <>
      {/* Hero Section */}
      <section className={styles.hero}>
        <div className="container">
          <div className={styles.heroGrid}>
            <div className={styles.heroImage}>
              {broker.thumbnail && (
                <img src={broker.thumbnail} alt={broker.title} />
              )}
            </div>
            <div className={styles.heroInfo}>
              <span className={styles.heroLabel}>Real Estate Professional</span>
              <h1 className={styles.heroName}>{broker.title}</h1>
              {broker.description && (
                <p className={styles.heroDesc}>{broker.description}</p>
              )}
              {broker.field?.phone && (
                <a href={`tel:${broker.field.phone}`} className={styles.heroPhone}>
                  {broker.field.phone}
                </a>
              )}
              {broker.field?.email && (
                <a href={`mailto:${broker.field.email}`} className={styles.heroEmail}>
                  {broker.field.email}
                </a>
              )}
            </div>
          </div>
        </div>
      </section>

      {/* Content */}
      {broker.content && (
        <section className={styles.content}>
          <div className="container">
            <div
              className={styles.richContent}
              dangerouslySetInnerHTML={{ __html: broker.content }}
            />
          </div>
        </section>
      )}

      {/* Contact Form */}
      <section className={styles.contact}>
        <div className="container">
          <div className={styles.contactInner}>
            <h2 className={styles.contactTitle}>Let&apos;s get in touch</h2>
            {submitted ? (
              <div className={styles.success}>
                <p>Message sent successfully!</p>
              </div>
            ) : (
              <form onSubmit={handleSubmit} className={styles.form}>
                <div className={styles.formRow}>
                  <input type="text" placeholder="First Name" required
                    value={formData.contacts}
                    onChange={e => setFormData({...formData, contacts: e.target.value})} />
                  <input type="text" placeholder="Last Name" required
                    value={formData.lastname}
                    onChange={e => setFormData({...formData, lastname: e.target.value})} />
                </div>
                <div className={styles.formRow}>
                  <input type="email" placeholder="Email Address" required
                    value={formData.email}
                    onChange={e => setFormData({...formData, email: e.target.value})} />
                  <input type="text" placeholder="Phone (Optional)"
                    value={formData.phone}
                    onChange={e => setFormData({...formData, phone: e.target.value})} />
                </div>
                <textarea placeholder="Message (Optional)" rows={4}
                  value={formData.message}
                  onChange={e => setFormData({...formData, message: e.target.value})} />
                <p className={styles.disclaimer}>
                  By submitting this form, you acknowledge that you accept the Privacy Policy and Terms of Use.
                </p>
                <button type="submit" className={styles.submitBtn}>
                  Send message <ArrowRight />
                </button>
              </form>
            )}
          </div>
        </div>
      </section>
    </>
  );
}
