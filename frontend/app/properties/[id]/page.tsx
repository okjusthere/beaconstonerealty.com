import Link from 'next/link';
import { notFound } from 'next/navigation';
import PropertyGallery from '@/components/PropertyGallery';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import { getGlobalData, getNewsDetail, getNewsList } from '@/lib/api';
import styles from './page.module.css';

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

const FORM_NOTE_HTML = `
  <p>Sending this form opens your email app with a prepared message to Beacon Stone Realty. By continuing, you acknowledge our <a href="/legal">Privacy Policy</a> and <a href="/legal">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>You can review and edit the draft before sending it from your own email account.</p>
`;

export async function generateStaticParams() {
  const properties = await getNewsList(5, -1, 1);
  return properties.map((property) => ({ id: String(property.id) }));
}

export default async function PropertyDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const propertyId = Number(id);

  if (!Number.isFinite(propertyId)) {
    notFound();
  }

  let property;
  try {
    property = await getNewsDetail(propertyId);
  } catch {
    notFound();
  }

  const [globalData, brokers] = await Promise.all([
    getGlobalData(),
    getNewsList(6, -1, 9),
  ]);

  const agentId = Number(property.field?.real_estate_agent_id || 0);
  const agent = brokers.find((item) => item.id === agentId);
  const recipientEmail = globalData.web_info.email || 'info@beacon-stone.com';
  const photos = property.photo_album?.length
    ? property.photo_album
    : property.thumbnail
      ? [property.thumbnail]
      : [];

  return (
    <>
      <PropertyGallery images={photos} title={property.title} />

      <section className={styles.details}>
        <div className="container">
          <div className={styles.detailsGrid}>
            <div className={styles.detailsMain}>
              <h1 className={styles.propertyTitle}>{property.title}</h1>
              {property.description && (
                <p className={styles.propertyDesc}>{property.description}</p>
              )}

              {property.field && Object.keys(property.field).length > 0 && (
                <div className={styles.fields}>
                  <h2 className={styles.fieldsSectionTitle}>Development Details</h2>
                  <div className={styles.fieldsGrid}>
                    {Object.entries(property.field).map(([key, value]) => (
                      value && (
                        <div key={key} className={styles.fieldItem}>
                          <span className={styles.fieldLabel}>
                            {key.replace(/_/g, ' ').replace(/\b\w/g, (letter) => letter.toUpperCase())}
                          </span>
                          <span className={styles.fieldValue}>{value}</span>
                        </div>
                      )
                    ))}
                  </div>
                </div>
              )}

              {property.content && (
                <div
                  className={styles.richContent}
                  dangerouslySetInnerHTML={{ __html: property.content }}
                />
              )}
            </div>

            <div className={styles.sidebar}>
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

              <div className={styles.contactForm}>
                <LegacyLeadForm
                  variant="inquiry"
                  submissionTitle={property.title}
                  title="Let's get in touch"
                  description="Tell us what you would like to know about this development and a prepared email draft will open for you."
                  messagePlaceholder="I would like to discuss this property with your team."
                  noteHtml={FORM_NOTE_HTML}
                  disclaimerHtml={FORM_DISCLAIMER_HTML}
                  compact
                  recipientEmail={recipientEmail}
                  successMessage="Your email app has been opened with a property inquiry draft."
                />
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
