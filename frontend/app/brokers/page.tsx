import Link from 'next/link';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { findMenuByPath, getGlobalData, getNewsList, type NewsItem } from '@/lib/api';

const FORM_NOTE_HTML = `
  <p>By submitting this form, you acknowledge that you accept our <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Use</a>.</p>
  <p>This site is protected by reCAPTCHA and the Google <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Service</a> apply.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>Yes, I would like more information from Beacon Stone Realty. Please use and/or share my information with a Beacon Stone Realty agent to contact me about my real estate needs.</p>
`;

export const metadata = {
  title: 'Our Agents | Beaconstone Realty',
  description: 'Meet the Beacon Stone Realty professionals guiding clients across New York City.',
};

type BrokerCard = Pick<NewsItem, 'id' | 'title' | 'url' | 'thumbnail' | 'keywords' | 'description' | 'content' | 'field'>;

export default async function BrokersPage() {
  let pageTitle = 'Guided by Expertise. Driven by Strategy';
  let bannerImage = '';
  let agents: BrokerCard[] = [];

  try {
    const [globalData, agentList] = await Promise.allSettled([
      getGlobalData(),
      getNewsList(6, -1, 9),
    ]);

    if (globalData.status === 'fulfilled') {
      const menu = findMenuByPath(globalData.value.menu_info, '/brokers');
      pageTitle = menu?.remarks || menu?.title || pageTitle;
      bannerImage = menu?.thumbnail || '';
    }
    if (agentList.status === 'fulfilled') {
      agents = agentList.value as BrokerCard[];
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
                      {agent.keywords && <p className={styles.agentRole}>{agent.keywords}</p>}
                      <div className={styles.separator} />
                      {agent.description && <p className={styles.agentOffice}>{agent.description}</p>}
                      {agent.content && (
                        <div
                          className={styles.agentContent}
                          dangerouslySetInnerHTML={{ __html: agent.content }}
                        />
                      )}
                    </div>
                    <div className={styles.agentContact}>
                      <span className={styles.contactTitle}>Contact</span>
                      {agent.field?.phone && (
                        <a href={`tel:${agent.field.phone}`} className={styles.contactLink}>O: {agent.field.phone}</a>
                      )}
                      {agent.field?.real_estate_broker_email && (
                        <a
                          href={`mailto:${agent.field.real_estate_broker_email}`}
                          className={styles.contactLink}
                        >
                          {agent.field.real_estate_broker_email}
                        </a>
                      )}
                      <Link href={agent.url || '#'} className={styles.contactAction}>Send message</Link>
                    </div>
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
              description="Tell us how one of our advisors can help. Once your request is reviewed, the team will follow up directly."
              messagePlaceholder="I would like to discuss buying, selling, or renting with your team."
              noteHtml={FORM_NOTE_HTML}
              disclaimerHtml={FORM_DISCLAIMER_HTML}
              successMessage="Thank you. Your request has been submitted."
            />
          </div>
        </div>
      </section>
    </>
  );
}
