'use client';

import { useState } from 'react';
import {
  buildMailtoHref,
  validateContactForm,
} from '@/lib/legacyForms';
import styles from '../app/contact/page.module.css';

interface ContactFormProps {
  recipientEmail: string;
}

export default function ContactForm({ recipientEmail }: ContactFormProps) {
  const [values, setValues] = useState({
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    budget: '',
    bedrooms: '',
    purchase: '',
    location: '',
  });
  const [feedback, setFeedback] = useState('');
  const [feedbackKind, setFeedbackKind] = useState<'error' | 'success'>('error');

  function setField(field: string, value: string) {
    setValues((prev) => ({ ...prev, [field]: value }));
  }

  function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setFeedback('');
    setFeedbackKind('error');

    const validation = validateContactForm(values, 'Contact Inquiry - Beacon Stone Realty');

    if (!validation.payload) {
      setFeedback(validation.error || 'Please fill in all required fields.');
      return;
    }

    window.location.href = buildMailtoHref(recipientEmail, validation.payload);
    setFeedback('Your email app has been opened with a contact request draft.');
    setFeedbackKind('success');
    setValues({
      firstName: '',
      lastName: '',
      email: '',
      phone: '',
      budget: '',
      bedrooms: '',
      purchase: '',
      location: '',
    });
  }

  return (
    <form className={styles.form} onSubmit={handleSubmit} noValidate>
      <div className={styles.formGrid}>
        <div className={styles.formRow}>
          <div className={styles.formField}>
            <label htmlFor="contact-firstName">First Name:</label>
            <input
              id="contact-firstName"
              type="text"
              value={values.firstName}
              onChange={(e) => setField('firstName', e.target.value)}
              placeholder="First Name"
            />
          </div>
          <div className={styles.formField}>
            <label htmlFor="contact-lastName">Last Name:</label>
            <input
              id="contact-lastName"
              type="text"
              value={values.lastName}
              onChange={(e) => setField('lastName', e.target.value)}
              placeholder="Last Name"
            />
          </div>
        </div>

        <div className={styles.formRow}>
          <div className={styles.formField}>
            <label htmlFor="contact-email">Email:</label>
            <input
              id="contact-email"
              type="email"
              value={values.email}
              onChange={(e) => setField('email', e.target.value)}
              placeholder="Email Address"
            />
          </div>
          <div className={styles.formField}>
            <label htmlFor="contact-phone">Phone:</label>
            <input
              id="contact-phone"
              type="text"
              value={values.phone}
              onChange={(e) => setField('phone', e.target.value)}
              placeholder="Phone Number"
            />
          </div>
        </div>

        <div className={styles.formRow}>
          <div className={styles.formField}>
            <label htmlFor="contact-budget">Budget:</label>
            <input
              id="contact-budget"
              type="text"
              value={values.budget}
              onChange={(e) => setField('budget', e.target.value)}
              placeholder="Budget"
            />
          </div>
          <div className={styles.formField}>
            <label htmlFor="contact-bedrooms">How Many Bedrooms:</label>
            <input
              id="contact-bedrooms"
              type="text"
              value={values.bedrooms}
              onChange={(e) => setField('bedrooms', e.target.value)}
              placeholder="Number of Bedrooms"
            />
          </div>
        </div>

        <div className={styles.formRow}>
          <div className={styles.formField}>
            <label htmlFor="contact-purchase">Purchase Time Line:</label>
            <input
              id="contact-purchase"
              type="text"
              value={values.purchase}
              onChange={(e) => setField('purchase', e.target.value)}
              placeholder="Purchase Timeline"
            />
          </div>
          <div className={styles.formField}>
            <label htmlFor="contact-location">Any Location Prefer:</label>
            <input
              id="contact-location"
              type="text"
              value={values.location}
              onChange={(e) => setField('location', e.target.value)}
              placeholder="Preferred Location"
            />
          </div>
        </div>
      </div>

      <button type="submit" className={styles.submitBtn}>
        SUBMIT
      </button>

      {feedback && (
        <p
          className={`${styles.feedback} ${
            feedbackKind === 'success' ? styles.feedbackSuccess : styles.feedbackError
          }`}
        >
          {feedback}
        </p>
      )}
    </form>
  );
}
