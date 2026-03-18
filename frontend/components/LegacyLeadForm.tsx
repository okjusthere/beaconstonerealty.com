'use client';

import { useState } from 'react';
import styles from './LegacyLeadForm.module.css';
import {
  buildMailtoHref,
  LegacyLeadVariant,
  validateContactForm,
  validateInquiryForm,
  validateJoinForm,
} from '@/lib/legacyForms';

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

interface LegacyLeadFormProps {
  variant: LegacyLeadVariant;
  submissionTitle: string;
  eyebrow?: string;
  title?: string;
  description?: string;
  descriptionHtml?: string;
  noteHtml?: string;
  disclaimerHtml?: string;
  messagePlaceholder?: string;
  successMessage?: string;
  compact?: boolean;
  recipientEmail?: string;
}

const INITIAL_VALUES: Record<LegacyLeadVariant, Record<string, string>> = {
  inquiry: {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    message: '',
  },
  join: {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    market: '',
    linkedin: '',
    message: '',
  },
  contact: {
    firstName: '',
    lastName: '',
    email: '',
    phone: '',
    budget: '',
    bedrooms: '',
    purchase: '',
    location: '',
  },
};

export default function LegacyLeadForm({
  variant,
  submissionTitle,
  eyebrow,
  title,
  description,
  descriptionHtml,
  noteHtml,
  disclaimerHtml,
  messagePlaceholder,
  successMessage = 'Your email app has been opened with a draft message.',
  compact = false,
  recipientEmail = 'info@beacon-stone.com',
}: LegacyLeadFormProps) {
  const [values, setValues] = useState<Record<string, string>>({ ...INITIAL_VALUES[variant] });
  const [feedback, setFeedback] = useState('');
  const [feedbackKind, setFeedbackKind] = useState<'error' | 'success'>('error');

  function setFieldValue(field: string, value: string) {
    setValues((current) => ({ ...current, [field]: value }));
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>) {
    event.preventDefault();
    setFeedback('');
    setFeedbackKind('error');

    const validation =
      variant === 'join'
        ? validateJoinForm(values as never, submissionTitle)
        : variant === 'contact'
          ? validateContactForm(values as never, submissionTitle)
          : validateInquiryForm(values as never, submissionTitle);

    if (!validation.payload) {
      setFeedback(validation.error || 'Failed to validate the form.');
      return;
    }

    window.location.href = buildMailtoHref(recipientEmail, validation.payload);
    setFeedback(successMessage);
    setFeedbackKind('success');
    setValues({ ...INITIAL_VALUES[variant] });
  }

  const shellClassName = [styles.shell, compact ? styles.compact : ''].filter(Boolean).join(' ');

  return (
    <div className={shellClassName}>
      {(eyebrow || title || description || descriptionHtml) && (
        <div className={styles.header}>
          {eyebrow && <p className={styles.eyebrow}>{eyebrow}</p>}
          {title && <h2 className={styles.title}>{title}</h2>}
          {description && <p className={styles.description}>{description}</p>}
          {!description && descriptionHtml && (
            <div className={styles.description} dangerouslySetInnerHTML={{ __html: descriptionHtml }} />
          )}
        </div>
      )}

      <form className={styles.form} onSubmit={handleSubmit} noValidate>
        <div className={styles.grid}>
          <div className={styles.field}>
            <label htmlFor={`${variant}-firstName`}>First Name</label>
            <input
              id={`${variant}-firstName`}
              type="text"
              value={values.firstName || ''}
              onChange={(event) => setFieldValue('firstName', event.target.value)}
              placeholder="First Name"
            />
          </div>
          <div className={styles.field}>
            <label htmlFor={`${variant}-lastName`}>Last Name</label>
            <input
              id={`${variant}-lastName`}
              type="text"
              value={values.lastName || ''}
              onChange={(event) => setFieldValue('lastName', event.target.value)}
              placeholder="Last Name"
            />
          </div>
          <div className={styles.field}>
            <label htmlFor={`${variant}-email`}>Email Address</label>
            <input
              id={`${variant}-email`}
              type="email"
              value={values.email || ''}
              onChange={(event) => setFieldValue('email', event.target.value)}
              placeholder="Email Address"
            />
          </div>
          <div className={styles.field}>
            <label htmlFor={`${variant}-phone`}>Phone Number</label>
            <input
              id={`${variant}-phone`}
              type="text"
              value={values.phone || ''}
              onChange={(event) => setFieldValue('phone', event.target.value)}
              placeholder="Phone Number"
            />
          </div>

          {variant === 'join' && (
            <>
              <div className={styles.field}>
                <label htmlFor={`${variant}-market`}>Select Market</label>
                <input
                  id={`${variant}-market`}
                  type="text"
                  value={values.market || ''}
                  onChange={(event) => setFieldValue('market', event.target.value)}
                  placeholder="Select Market"
                />
              </div>
              <div className={styles.field}>
                <label htmlFor={`${variant}-linkedin`}>LinkedIn URL</label>
                <input
                  id={`${variant}-linkedin`}
                  type="text"
                  value={values.linkedin || ''}
                  onChange={(event) => setFieldValue('linkedin', event.target.value)}
                  placeholder="LinkedIn URL"
                />
              </div>
            </>
          )}

          {variant === 'contact' && (
            <>
              <div className={styles.field}>
                <label htmlFor={`${variant}-budget`}>Budget</label>
                <input
                  id={`${variant}-budget`}
                  type="text"
                  value={values.budget || ''}
                  onChange={(event) => setFieldValue('budget', event.target.value)}
                  placeholder="Budget"
                />
              </div>
              <div className={styles.field}>
                <label htmlFor={`${variant}-bedrooms`}>How Many Bedrooms</label>
                <input
                  id={`${variant}-bedrooms`}
                  type="text"
                  value={values.bedrooms || ''}
                  onChange={(event) => setFieldValue('bedrooms', event.target.value)}
                  placeholder="How Many Bedrooms"
                />
              </div>
              <div className={styles.field}>
                <label htmlFor={`${variant}-purchase`}>Purchase Time Line</label>
                <input
                  id={`${variant}-purchase`}
                  type="text"
                  value={values.purchase || ''}
                  onChange={(event) => setFieldValue('purchase', event.target.value)}
                  placeholder="Purchase Time Line"
                />
              </div>
              <div className={styles.field}>
                <label htmlFor={`${variant}-location`}>Any Location Prefer</label>
                <input
                  id={`${variant}-location`}
                  type="text"
                  value={values.location || ''}
                  onChange={(event) => setFieldValue('location', event.target.value)}
                  placeholder="Any Location Prefer"
                />
              </div>
            </>
          )}

          {variant !== 'contact' && (
            <div className={`${styles.field} ${styles.fullWidth}`}>
              <label htmlFor={`${variant}-message`}>Message</label>
              <textarea
                id={`${variant}-message`}
                rows={4}
                value={values.message || ''}
                onChange={(event) => setFieldValue('message', event.target.value)}
                placeholder={messagePlaceholder || 'I would like to discuss buying, selling, renting with you.'}
              />
            </div>
          )}

        </div>

        {noteHtml && <div className={styles.note} dangerouslySetInnerHTML={{ __html: noteHtml }} />}
        {disclaimerHtml && <div className={styles.disclaimer} dangerouslySetInnerHTML={{ __html: disclaimerHtml }} />}

        <button type="submit" className={styles.submit}>
          Send Message
          <ArrowRight />
        </button>

        {feedback && (
          <p
            className={[
              styles.feedback,
              feedbackKind === 'success' ? styles.feedbackSuccess : styles.feedbackError,
            ].join(' ')}
          >
            {feedback}
          </p>
        )}
      </form>
    </div>
  );
}
