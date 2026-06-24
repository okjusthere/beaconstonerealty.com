import Link from 'next/link';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { findMenuByPath, getGlobalData, type NewsItem } from '@/lib/api';
import { getSanityAgentList } from '@/lib/sanity-api';

const FORM_NOTE_HTML = `
  <p>By submitting this form, you agree to our <a href="/legal">Privacy Policy</a> and <a href="/legal">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>Our team will review your inquiry and get back to you shortly.</p>
`;

export const metadata = {
  title: 'Real Estate Advisors | Beaconstone Realty',
  description: 'Meet the Beacon Stone Realty professionals guiding clients across New York City.',
};

type BrokerCard = Pick<NewsItem, 'id' | 'title' | 'url' | 'thumbnail' | 'keywords' | 'description' | 'content' | 'field'>;

type BrokerContact = {
  phone: string;
  email: string;
  phoneLabel: string;
  emailLabel: string;
};

function getBrokerRole(agent: BrokerCard): string {
  return agent.keywords || agent.description || '';
}

function getBrokerOffice(agent: BrokerCard): string {
  if (agent.keywords && agent.description && agent.description !== agent.keywords) {
    return agent.description;
  }

  return '';
}

function getBrokerBio(agent: BrokerCard): string {
  return agent.field?.real_estate_broker_desc || agent.content || '';
}

function normalizePhone(value?: string): string {
  if (!value) {
    return '';
  }

  return value
    .replace(/\s+/g, ' ')
    .replace(/^\+1\s+\+1\b/, '+1')
    .trim();
}

function getBrokerContact(agent: BrokerCard, officePhone: string, officeEmail: string): BrokerContact {
  const directPhone = normalizePhone(agent.field?.phone);
  const directEmail = agent.field?.real_estate_broker_email || agent.field?.email || '';
  const fallbackPhone = normalizePhone(officePhone);
  const fallbackEmail = officeEmail || '';

  return {
    phone: directPhone || fallbackPhone,
    email: directEmail || fallbackEmail,
    phoneLabel: directPhone ? 'Cell' : 'Cell',
    emailLabel: directEmail ? 'Direct' : 'Office',
  };
}

export default async function BrokersPage() {
  let pageTitle = 'Guided by Expertise. Driven by Strategy';
  let bannerImage = '';
  let agents: BrokerCard[] = [];
  let officePhone = '';
  let officeEmail = '';
  let recipientEmail = 'info@beacon-stone.com';

  try {
    const [globalData, sanityAgents] = await Promise.allSettled([
      getGlobalData(),
      getSanityAgentList(),
    ]);

    if (globalData.status === 'fulfilled') {
      const menu = findMenuByPath(globalData.value.menu_info, '/brokers');
      pageTitle = menu?.remarks || menu?.title || pageTitle;
      bannerImage = menu?.thumbnail || '';
      officePhone = globalData.value.web_info.phone || '';
      officeEmail = globalData.value.web_info.email || '';
      recipientEmail = officeEmail || recipientEmail;
    }
    if (sanityAgents.status === 'fulfilled' && sanityAgents.value.length > 0) {
      agents = sanityAgents.value as BrokerCard[];
    }
  } catch {
    // Preserve a readable empty state if the data source is unavailable.
  }

  return (
    <>
      <section className={styles.hero}>
        {bannerImage ? (
          <img src={bannerImage} alt={pageTitle} className={styles.heroImage} />
        ) : (
          <div className={styles.heroFallback} />
        )}
        <div className={styles.heroOverlay} />
        <div className={styles.heroContent}>
          <h1 className={styles.heroTitle}>{pageTitle}</h1>
        </div>
      </section>

      <section className={styles.results}>
        <div className="container">
          {agents.length > 0 ? (
            <div className={styles.resultList}>
              {agents.map((agent) => {
                const detailUrl = agent.url || '#';
                const contactUrl = agent.url ? `${agent.url}#contact` : '#';

                return (
                  <article key={agent.url || `${agent.id}-${agent.title}`} className={styles.agentCard}>
                    <Link href={detailUrl} className={styles.agentImageLink}>
                      {agent.thumbnail && (
                        <img
                          src={agent.thumbnail}
                          alt={agent.title}
                          className={styles.agentImage}
                          loading="lazy"
                        />
                      )}
                    </Link>
                    <div className={styles.agentBody}>
                      <div className={styles.agentMain}>
                        <Link href={detailUrl} className={styles.agentName}>{agent.title}</Link>
                        {getBrokerRole(agent) && <p className={styles.agentRole}>{getBrokerRole(agent)}</p>}
                        <div className={styles.separator} />
                        {getBrokerOffice(agent) && <p className={styles.agentOffice}>{getBrokerOffice(agent)}</p>}
                        {getBrokerBio(agent) && (
                          <div
                            className={styles.agentContent}
                            dangerouslySetInnerHTML={{ __html: getBrokerBio(agent) }}
                          />
                        )}
                      </div>
                      {(() => {
                        const contact = getBrokerContact(agent, officePhone, officeEmail);

                        return (
                          <div className={styles.agentContact}>
                            <span className={styles.contactTitle}>Contact</span>
                            {contact.phone && (
                              <a href={`tel:${contact.phone}`} className={styles.contactLink}>
                                {contact.phoneLabel}: {contact.phone}
                              </a>
                            )}
                            {contact.email && (
                              <a
                                href={`mailto:${contact.email}`}
                                className={styles.contactLink}
                              >
                                {contact.email}
                              </a>
                            )}
                            <Link href={contactUrl} className={styles.contactAction}>Send message</Link>
                          </div>
                        );
                      })()}
                    </div>
                  </article>
                );
              })}
            </div>
          ) : (
            <div className={styles.empty}>
              <p>No agents found. Please check back later.</p>
            </div>
          )}
        </div>
      </section>

      <section className={styles.touchSection}>
        <div className="container">
          <div className={styles.touchWrap}>
            <LegacyLeadForm
              variant="inquiry"
              submissionTitle={pageTitle}
              title="Let's get in touch"
              description="Tell us how one of our advisors can help and your email app will open with a prepared message."
              messagePlaceholder="I would like to discuss buying, selling, or renting with your team."
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
