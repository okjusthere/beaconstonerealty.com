'use client';

import { useState } from 'react';
import { validateContactForm } from '@/lib/legacyForms';
import { submitForm } from '@/lib/formSubmit';
import styles from '../app/contact/page.module.css';

export default function ContactForm() {
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
  const [submitting, setSubmitting] = useState(false);

  function setField(field: string, value: string) {
    setValues((prev) => ({ ...prev, [field]: value }));
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setFeedback('');
    setFeedbackKind('error');

    const validation = validateContactForm(values, 'Contact Inquiry - Beacon Stone Realty');

    if (!validation.payload) {
      setFeedback(validation.error || 'Please fill in all required fields.');
      return;
    }

    setSubmitting(true);
    const result = await submitForm({
      type: 'contact',
      firstName: values.firstName,
      lastName: values.lastName,
      email: values.email,
      phone: values.phone,
      metadata: {
        budget: values.budget,
        bedrooms: values.bedrooms,
        timeline: values.purchase,
        location: values.location,
      },
    });
    setSubmitting(false);

    if (result.success) {
      setFeedback('Thank you! We will be in touch shortly.');
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
    } else {
      setFeedback(result.error || 'Something went wrong. Please try again.');
      setFeedbackKind('error');
    }
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

      <button type="submit" className={styles.submitBtn} disabled={submitting}>
        {submitting ? 'SUBMITTING...' : 'SUBMIT'}
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
