import { notFound } from 'next/navigation';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import { getGlobalData } from '@/lib/api';
import { getSanityAgentDetail, getSanityAgentIds } from '@/lib/sanity-api';
import styles from './page.module.css';

const FORM_NOTE_HTML = `
  <p>By submitting this form, you agree to our <a href="/legal">Privacy Policy</a> and <a href="/legal">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>Our team will review your inquiry and get back to you shortly.</p>
`;

export async function generateStaticParams() {
  const ids = await getSanityAgentIds();
  return ids.map((id) => ({ id }));
}

export default async function BrokerDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const brokerId = Number(id);

  if (!Number.isFinite(brokerId)) {
    notFound();
  }

  const broker = await getSanityAgentDetail(brokerId);
  if (!broker) {
    notFound();
  }

  const globalData = await getGlobalData();
  const brokerEmail = broker.field?.real_estate_broker_email || broker.field?.email || '';
  const recipientEmail = brokerEmail || globalData.web_info.email || 'info@beacon-stone.com';

  return (
    <>
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
                  Cell: {broker.field.phone}
                </a>
              )}
              {brokerEmail && (
                <a href={`mailto:${brokerEmail}`} className={styles.heroEmail}>
                  {brokerEmail}
                </a>
              )}
            </div>
          </div>
        </div>
      </section>

      {(broker.field?.real_estate_broker_desc || broker.content) && (
        <section className={styles.content}>
          <div className="container">
            <div
              className={styles.richContent}
              dangerouslySetInnerHTML={{ __html: broker.field?.real_estate_broker_desc || broker.content || '' }}
            />
          </div>
        </section>
      )}

      <section className={styles.contact}>
        <div className="container">
          <div className={styles.contactInner}>
            <LegacyLeadForm
              variant="inquiry"
              submissionTitle={broker.title}
              title="Let's get in touch"
              description="Tell us how this advisor can help and your email app will open with a prepared message."
              messagePlaceholder="I would like to discuss buying, selling, or renting with you."
              noteHtml={FORM_NOTE_HTML}
              disclaimerHtml={FORM_DISCLAIMER_HTML}
              recipientEmail={recipientEmail}
              successMessage="Thank you! Your inquiry has been submitted successfully."
            />
          </div>
        </div>
      </section>
    </>
  );
}
