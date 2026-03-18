import { notFound } from 'next/navigation';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import { getGlobalData, getNewsDetail, getNewsList } from '@/lib/api';
import styles from './page.module.css';

const FORM_NOTE_HTML = `
  <p>Sending this form opens your email app with a prepared message to Beacon Stone Realty. By continuing, you acknowledge our <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>You can review and edit the draft before sending it from your own email account.</p>
`;

export async function generateStaticParams() {
  const brokers = await getNewsList(6, -1, 9);
  return brokers.map((broker) => ({ id: String(broker.id) }));
}

export default async function BrokerDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const brokerId = Number(id);

  if (!Number.isFinite(brokerId)) {
    notFound();
  }

  let broker;
  try {
    broker = await getNewsDetail(brokerId);
  } catch {
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
                  {broker.field.phone}
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
              successMessage="Your email app has been opened with an advisor inquiry draft."
            />
          </div>
        </div>
      </section>
    </>
  );
}
