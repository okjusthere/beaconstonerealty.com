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

export default function PropertyDetailPage() {
  const [property, setProperty] = useState<PropertyData | null>(null);
  const [agent, setAgent] = useState<AgentData | null>(null);
  const [activePhoto, setActivePhoto] = useState(0);
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
      .then(data => setProperty(data))
      .catch(() => {});

    // Try to load agent data
    fetch(`${apiBase}/application/index/news_list.php?id=3&top=1&type=6`)
      .then(r => r.json())
      .then(data => { if (Array.isArray(data) && data.length > 0) setAgent(data[0]); })
      .catch(() => {});
  }, []);

  const photos = property?.photo_album?.length ? property.photo_album : property?.thumbnail ? [property.thumbnail] : [];

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
                <h3 className={styles.contactTitle}>Let&apos;s get in touch</h3>
                {submitted ? (
                  <div className={styles.success}>
                    <p>Message sent successfully!</p>
                  </div>
                ) : (
                  <form onSubmit={handleSubmit}>
                    <div className={styles.inputGroup}>
                      <input type="text" placeholder="First Name" required
                        value={formData.contacts}
                        onChange={e => setFormData({...formData, contacts: e.target.value})} />
                    </div>
                    <div className={styles.inputGroup}>
                      <input type="text" placeholder="Last Name" required
                        value={formData.lastname}
                        onChange={e => setFormData({...formData, lastname: e.target.value})} />
                    </div>
                    <div className={styles.inputGroup}>
                      <input type="email" placeholder="Email Address" required
                        value={formData.email}
                        onChange={e => setFormData({...formData, email: e.target.value})} />
                    </div>
                    <div className={styles.inputGroup}>
                      <input type="text" placeholder="Phone (Optional)"
                        value={formData.phone}
                        onChange={e => setFormData({...formData, phone: e.target.value})} />
                    </div>
                    <div className={styles.inputGroup}>
                      <textarea placeholder="Message (Optional)" rows={4}
                        value={formData.message}
                        onChange={e => setFormData({...formData, message: e.target.value})} />
                    </div>
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
          </div>
        </div>
      </section>
    </>
  );
}
