'use client';

import { useState, useEffect } from 'react';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { useParams } from 'next/navigation';

interface BrokerData {
  title: string;
  description: string;
  content: string;
  thumbnail: string;
  keywords: string;
  field?: Record<string, string>;
}

const FORM_NOTE_HTML = `
  <p>By submitting this form, you acknowledge that you accept our <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Use</a>.</p>
  <p>This site is protected by reCAPTCHA and the Google <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Service</a> apply.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>Yes, I would like more information from Beacon Stone Realty. Please use and/or share my information with a Beacon Stone Realty agent to contact me about my real estate needs.</p>
`;

export default function BrokerDetailPage() {
  const params = useParams<{ id: string }>();
  const routeId = Array.isArray(params?.id) ? params.id[0] : params?.id;
  const [broker, setBroker] = useState<BrokerData | null>(null);

  useEffect(() => {
    const id = routeId;
    if (!id) return;

    let cancelled = false;

    async function loadBroker() {
      try {
        const response = await fetch(`/api/legacy/news_detail?id=${encodeURIComponent(id)}`);
        const payload = await response.json();
        if (!cancelled) {
          setBroker(payload?.obj?.data ?? null);
        }
      } catch {
        if (!cancelled) {
          setBroker(null);
        }
      }
    }

    void loadBroker();

    return () => {
      cancelled = true;
    };
  }, [routeId]);

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
            <LegacyLeadForm
              variant="inquiry"
              submissionTitle={broker.title}
              title="Let's get in touch"
              description="Tell us how this advisor can help and the team will review your request directly."
              messagePlaceholder="I would like to discuss buying, selling, or renting with you."
              noteHtml={FORM_NOTE_HTML}
              disclaimerHtml={FORM_DISCLAIMER_HTML}
              successMessage="Thank you. Your agent inquiry has been submitted."
            />
          </div>
        </div>
      </section>
    </>
  );
}
