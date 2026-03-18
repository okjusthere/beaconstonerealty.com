'use client';

import { useState } from 'react';
import styles from './page.module.css';

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
}

export default function SellWithUsPage() {
  const [formData, setFormData] = useState({
    firstname: '',
    lastname: '',
    email: '',
    phone: '',
    market: '',
    linkedin: '',
    message: '',
  });
  const [submitted, setSubmitted] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    try {
      const apiBase = process.env.NEXT_PUBLIC_API_BASE_URL || '';
      await fetch(`${apiBase}/application/index/inner_message.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams({
          contacts2: formData.firstname,
          lastname2: formData.lastname,
          email2: formData.email,
          phone2: formData.phone,
          SelectMarket: formData.market,
          LinkedIn: formData.linkedin,
          message2: formData.message,
        }).toString(),
      });
      setSubmitted(true);
    } catch {
      alert('Failed to send message. Please try again.');
    }
  };

  return (
    <>
      {/* Hero */}
      <section className={styles.hero}>
        <div className="container">
          <h1 className={styles.heroTitle}>Sell With Us</h1>
          <p className={styles.heroDesc}>
            Partner with Beaconstone Realty for a premium selling experience backed by global reach and local expertise.
          </p>
        </div>
      </section>

      {/* Form Section */}
      <section className={`section-lg ${styles.formSection}`}>
        <div className="container">
          <div className={styles.formGrid}>
            <div className={styles.formInfo}>
              <h2 className={styles.formTitle}>Join Our Network</h2>
              <p className={styles.formDesc}>
                Whether you&apos;re looking to list your property or join as an agent,
                we&apos;d love to hear from you. Fill out the form and our team will
                be in touch shortly.
              </p>
              <div className={styles.formFeatures}>
                <div className={styles.feature}>
                  <div className={styles.featureIcon}>🏠</div>
                  <div>
                    <h4>Global Reach</h4>
                    <p>Access to international buyers and sellers</p>
                  </div>
                </div>
                <div className={styles.feature}>
                  <div className={styles.featureIcon}>📊</div>
                  <div>
                    <h4>Market Expertise</h4>
                    <p>In-depth local market knowledge and analysis</p>
                  </div>
                </div>
                <div className={styles.feature}>
                  <div className={styles.featureIcon}>🤝</div>
                  <div>
                    <h4>White-Glove Service</h4>
                    <p>Dedicated support throughout the entire process</p>
                  </div>
                </div>
              </div>
            </div>

            <div className={styles.formCard}>
              {submitted ? (
                <div className={styles.success}>
                  <h3>Thank You!</h3>
                  <p>Your message has been sent successfully. We&apos;ll be in touch soon.</p>
                </div>
              ) : (
                <form onSubmit={handleSubmit} className={styles.form}>
                  <div className={styles.formRow}>
                    <div className={styles.inputGroup}>
                      <label htmlFor="firstname">First Name *</label>
                      <input
                        id="firstname"
                        type="text"
                        required
                        value={formData.firstname}
                        onChange={e => setFormData({ ...formData, firstname: e.target.value })}
                      />
                    </div>
                    <div className={styles.inputGroup}>
                      <label htmlFor="lastname">Last Name *</label>
                      <input
                        id="lastname"
                        type="text"
                        required
                        value={formData.lastname}
                        onChange={e => setFormData({ ...formData, lastname: e.target.value })}
                      />
                    </div>
                  </div>
                  <div className={styles.formRow}>
                    <div className={styles.inputGroup}>
                      <label htmlFor="email">Email Address *</label>
                      <input
                        id="email"
                        type="email"
                        required
                        value={formData.email}
                        onChange={e => setFormData({ ...formData, email: e.target.value })}
                      />
                    </div>
                    <div className={styles.inputGroup}>
                      <label htmlFor="phone">Phone Number</label>
                      <input
                        id="phone"
                        type="text"
                        value={formData.phone}
                        onChange={e => setFormData({ ...formData, phone: e.target.value })}
                      />
                    </div>
                  </div>
                  <div className={styles.formRow}>
                    <div className={styles.inputGroup}>
                      <label htmlFor="market">Select Market</label>
                      <input
                        id="market"
                        type="text"
                        value={formData.market}
                        onChange={e => setFormData({ ...formData, market: e.target.value })}
                      />
                    </div>
                    <div className={styles.inputGroup}>
                      <label htmlFor="linkedin">LinkedIn URL</label>
                      <input
                        id="linkedin"
                        type="text"
                        value={formData.linkedin}
                        onChange={e => setFormData({ ...formData, linkedin: e.target.value })}
                      />
                    </div>
                  </div>
                  <div className={styles.inputGroup}>
                    <label htmlFor="message">Message</label>
                    <textarea
                      id="message"
                      rows={4}
                      value={formData.message}
                      onChange={e => setFormData({ ...formData, message: e.target.value })}
                    />
                  </div>
                  <p className={styles.disclaimer}>
                    By submitting this form, you acknowledge that you accept our Privacy Policy and Terms of Use.
                  </p>
                  <button type="submit" className="btn btn-primary">
                    Send Message <ArrowRight />
                  </button>
                </form>
              )}
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
