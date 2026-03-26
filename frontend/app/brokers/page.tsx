import Link from 'next/link';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { findMenuByPath, getGlobalData, getNewsDetail, getNewsList, type NewsItem } from '@/lib/api';

const FORM_NOTE_HTML = `
  <p>Sending this form opens your email app with a prepared message to Beacon Stone Realty. By continuing, you acknowledge our <a href="/legal">Privacy Policy</a> and <a href="/legal">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>You can review and edit the draft before sending it from your own email account.</p>
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

function mergeBrokerRecord(base: BrokerCard, detail: NewsItem): BrokerCard {
  return {
    ...base,
    ...detail,
    url: base.url || detail.url,
    thumbnail: base.thumbnail || detail.thumbnail,
    description: base.description || detail.description,
    content: detail.content || base.content,
    field: {
      ...(base.field || {}),
      ...(detail.field || {}),
    },
  };
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
    const [globalData, agentList] = await Promise.allSettled([
      getGlobalData(),
      getNewsList(6, -1, 9),
    ]);

    if (globalData.status === 'fulfilled') {
      const menu = findMenuByPath(globalData.value.menu_info, '/brokers');
      pageTitle = menu?.remarks || menu?.title || pageTitle;
      bannerImage = menu?.thumbnail || '';
      officePhone = globalData.value.web_info.phone || '';
      officeEmail = globalData.value.web_info.email || '';
      recipientEmail = officeEmail || recipientEmail;
    }
    if (agentList.status === 'fulfilled') {
      const mergedAgents = await Promise.all(
        (agentList.value as BrokerCard[]).map(async (agent) => {
          try {
            const detail = await getNewsDetail(agent.id);
            return mergeBrokerRecord(agent, detail);
          } catch {
            return agent;
          }
        }),
      );
      agents = mergedAgents;
    }
  } catch {
    // Preserve a readable empty state if the legacy feed is unavailable.
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
              {agents.map((agent) => (
                <article key={agent.id} className={styles.agentCard}>
                  <Link href={agent.url || '#'} className={styles.agentImageLink}>
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
                      <Link href={agent.url || '#'} className={styles.agentName}>{agent.title}</Link>
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
                          <Link href={agent.url || '#'} className={styles.contactAction}>Send message</Link>
                        </div>
                      );
                    })()}
                  </div>
                </article>
              ))}
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
              successMessage="Your email app has been opened with an inquiry draft."
            />
          </div>
        </div>
      </section>
    </>
  );
}
