'use client';

import { useState, useEffect } from 'react';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import Link from 'next/link';
import { useParams } from 'next/navigation';

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
}

interface PropertyData {
  title: string;
  description: string;
  content: string;
  thumbnail: string;
  photo_album: string[];
  field?: Record<string, string>;
}

interface AgentData {
  title: string;
  thumbnail: string;
  url: string;
  description: string;
  field?: Record<string, string>;
}

const FORM_NOTE_HTML = `
  <p>By submitting this form, you acknowledge that you accept our <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Use</a>.</p>
  <p>This site is protected by reCAPTCHA and the Google <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Service</a> apply.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>Yes, I would like more information from Beacon Stone Realty. Please use and/or share my information with a Beacon Stone Realty agent to contact me about my real estate needs.</p>
`;

export default function PropertyDetailPage() {
  const params = useParams<{ id: string }>();
  const routeId = Array.isArray(params?.id) ? params.id[0] : params?.id;
  const [property, setProperty] = useState<PropertyData | null>(null);
  const [agent, setAgent] = useState<AgentData | null>(null);
  const [activePhoto, setActivePhoto] = useState(0);

  useEffect(() => {
    const id = routeId;
    if (!id) return;

    let cancelled = false;

    async function loadProperty() {
      try {
        const response = await fetch(`/api/legacy/news_detail?id=${encodeURIComponent(id)}`);
        const payload = await response.json();
        const propertyData = payload?.obj?.data ?? null;
        if (!cancelled) {
          setProperty(propertyData);
        }

        const agentId = propertyData?.field?.real_estate_agent_id;
        if (agentId) {
          const agentResponse = await fetch(`/api/legacy/news_detail?id=${encodeURIComponent(agentId)}`);
          const agentPayload = await agentResponse.json();
          if (!cancelled) {
            setAgent(agentPayload?.obj?.data ?? null);
          }
        }
      } catch {
        if (!cancelled) {
          setProperty(null);
        }
      }
    }

    void loadProperty();

    return () => {
      cancelled = true;
    };
  }, [routeId]);

  const photos = property?.photo_album?.length ? property.photo_album : property?.thumbnail ? [property.thumbnail] : [];

  if (!property) {
    return (
      <div className={styles.loading}>
        <div className={styles.spinner} />
        <p>Loading property details...</p>
      </div>
    );
  }

  return (
    <>
      {/* Photo Gallery */}
      <section className={styles.gallery}>
        <div className={styles.galleryMain}>
          {photos.length > 0 && (
            <img
              src={photos[activePhoto]}
              alt={property.title}
              className={styles.galleryImage}
            />
          )}
        </div>
        {photos.length > 1 && (
          <div className={styles.galleryThumbs}>
            {photos.map((photo, i) => (
              <button
                key={i}
                className={`${styles.thumbBtn} ${i === activePhoto ? styles.thumbActive : ''}`}
                onClick={() => setActivePhoto(i)}
              >
                <img src={photo} alt={`Photo ${i + 1}`} />
              </button>
            ))}
          </div>
        )}
      </section>

      {/* Property Info */}
      <section className={styles.details}>
        <div className="container">
          <div className={styles.detailsGrid}>
            {/* Main Content */}
            <div className={styles.detailsMain}>
              <h1 className={styles.propertyTitle}>{property.title}</h1>
              {property.description && (
                <p className={styles.propertyDesc}>{property.description}</p>
              )}

              {/* Property Fields */}
              {property.field && Object.keys(property.field).length > 0 && (
                <div className={styles.fields}>
                  <h2 className={styles.fieldsSectionTitle}>Development Details</h2>
                  <div className={styles.fieldsGrid}>
                    {Object.entries(property.field).map(([key, value]) => (
                      value && (
                        <div key={key} className={styles.fieldItem}>
                          <span className={styles.fieldLabel}>
                            {key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                          </span>
                          <span className={styles.fieldValue}>{value}</span>
                        </div>
                      )
                    ))}
                  </div>
                </div>
              )}

              {/* Rich Content */}
              {property.content && (
                <div
                  className={styles.richContent}
                  dangerouslySetInnerHTML={{ __html: property.content }}
                />
              )}
            </div>

            {/* Sidebar: Agent + Contact Form */}
            <div className={styles.sidebar}>
              {/* Agent Card */}
              {agent && (
                <div className={styles.agentCard}>
                  <div className={styles.agentPhoto}>
                    {agent.thumbnail && <img src={agent.thumbnail} alt={agent.title} />}
                  </div>
                  <div className={styles.agentInfo}>
                    <h3 className={styles.agentName}>{agent.title}</h3>
                    <p className={styles.agentRole}>Real Estate Professional</p>
                    {agent.url && (
                      <Link href={agent.url} className={styles.agentLink}>
                        Agent Profile <ArrowRight />
                      </Link>
                    )}
                  </div>
                </div>
              )}

              {/* Contact Form */}
              <div className={styles.contactForm}>
                <LegacyLeadForm
                  variant="inquiry"
                  submissionTitle={property.title}
                  title="Let's get in touch"
                  description="Tell us what you would like to know about this development and one of our advisors will review your request."
                  messagePlaceholder="I would like to discuss this property with your team."
                  noteHtml={FORM_NOTE_HTML}
                  disclaimerHtml={FORM_DISCLAIMER_HTML}
                  compact
                  successMessage="Thank you. Your property inquiry has been submitted."
                />
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
