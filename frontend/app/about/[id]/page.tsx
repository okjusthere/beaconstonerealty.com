import Link from 'next/link';
import styles from './page.module.css';
import { getGlobalData, getMenuRouteIds, getNewsDetail, getNewsList, getNewsRouteIds, type NewsItem } from '@/lib/api';

function ArrowRight() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

function stripHtmlTags(html: string): string {
  return html
    .replace(/<br\s*\/?>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/&amp;/gi, '&')
    .replace(/&quot;/gi, '"')
    .replace(/&#39;/gi, "'")
    .replace(/\s+/g, ' ')
    .trim();
}

type StatItem = { id: number; title: string; keywords: string; description: string };
type NetworkCard = Pick<NewsItem, 'id' | 'title' | 'thumbnail' | 'url'>;
type AdvisorCard = Pick<NewsItem, 'id' | 'title' | 'url' | 'thumbnail' | 'description' | 'field'>;
type DiscoverCard = Pick<NewsItem, 'id' | 'title' | 'url' | 'thumbnail' | 'description'>;

export default async function AboutPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let heroTitle = 'About Us';
  let heroDesc = '';
  let stats: StatItem[] = [];
  let foundationTitle = '';
  let foundationContent = '';
  let featureImage = '';
  let featureTitle = '';
  let featureContent = '';
  let networkTitle = '';
  let networkDesc = '';
  let networkCards: NetworkCard[] = [];
  let advisorsTitle = 'Advisors, Not Just Agents';
  let advisors: AdvisorCard[] = [];
  let discoverCards: DiscoverCard[] = [];

  try {
    const [
      globalData,
      aboutHero,
      aboutStats,
      aboutFoundation,
      aboutFeature,
      networkData,
      advisorsData,
      discoverData,
    ] = await Promise.allSettled([
      getGlobalData(),
      getNewsDetail(13),
      getNewsList(2, -1, 2),
      getNewsDetail(18),
      getNewsDetail(19),
      getNewsList(3, -1, 5),
      getNewsList(6, -1, 6),
      getNewsList(10, 2, 10),
    ]);

    if (globalData.status === 'fulfilled') {
      const aboutMenu = globalData.value.menu_info.find((item) => item.url === `/about/${id}`);
      if (aboutMenu) {
        heroTitle = aboutMenu.sub_title || aboutMenu.title || heroTitle;
        heroDesc = aboutMenu.remarks || '';
      }

      const networkClass = globalData.value.news_class_info.find((item) => item.id === 3);
      if (networkClass) {
        networkTitle = networkClass.title;
        networkDesc = networkClass.description;
      }

      const advisorClass = globalData.value.news_class_info.find((item) => item.id === 4);
      if (advisorClass?.title) {
        advisorsTitle = advisorClass.title;
      }
    }

    if (aboutHero.status === 'fulfilled') {
      heroTitle ||= aboutHero.value.title;
      heroDesc ||= aboutHero.value.description;
    }

    if (aboutStats.status === 'fulfilled') {
      stats = aboutStats.value as StatItem[];
    }

    if (aboutFoundation.status === 'fulfilled') {
      foundationTitle = aboutFoundation.value.title;
      foundationContent = aboutFoundation.value.description || aboutFoundation.value.content || '';
    }

    if (aboutFeature.status === 'fulfilled') {
      featureImage = aboutFeature.value.thumbnail;
      featureTitle = aboutFeature.value.title;
      featureContent = aboutFeature.value.description || aboutFeature.value.content || '';
    }

    if (networkData.status === 'fulfilled') {
      networkCards = networkData.value as NetworkCard[];
    }

    if (advisorsData.status === 'fulfilled') {
      advisors = advisorsData.value as AdvisorCard[];
    }

    if (discoverData.status === 'fulfilled') {
      discoverCards = discoverData.value as DiscoverCard[];
    }
  } catch {
    // Keep static fallbacks intact.
  }

  return (
    <>
      <section className={styles.hero}>
        <div className={styles.heroInfo}>
          <div className="container">
            <h1 className={styles.heroTitle}>{heroTitle}</h1>
            {heroDesc && <p className={styles.heroDesc}>{heroDesc}</p>}
          </div>
        </div>
      </section>

      {stats.length > 0 && (
        <section className={styles.stats}>
          <div className="container">
            <div className={styles.statsGrid}>
              {stats.map((stat) => (
                <div key={stat.id} className={styles.statItem}>
                  <span className={styles.statLabel}>{stat.title}</span>
                  <span className={styles.statValue}>{stat.keywords}</span>
                  <span className={styles.statDesc}>{stat.description}</span>
                </div>
              ))}
            </div>
          </div>
        </section>
      )}

      {(foundationTitle || foundationContent) && (
        <section className={styles.foundation}>
          <div className="container">
            <div className={styles.foundationInner}>
              <h2 className={styles.sectionTitle}>{foundationTitle}</h2>
              <div className={styles.richText} dangerouslySetInnerHTML={{ __html: foundationContent }} />
            </div>
          </div>
        </section>
      )}

      {(featureTitle || featureImage || featureContent) && (
        <section className={styles.feature}>
          <div className={styles.featureMedia}>
            <div className="container">
              {featureImage && <img src={featureImage} alt={featureTitle || 'About Beacon Stone Realty'} loading="lazy" />}
            </div>
          </div>
          <div className={styles.featureText}>
            <div className="container">
              <div className={styles.featureTextInner}>
                <h2>{featureTitle}</h2>
                <div dangerouslySetInnerHTML={{ __html: featureContent }} />
              </div>
            </div>
          </div>
        </section>
      )}

      {(networkTitle || networkDesc || networkCards.length > 0) && (
        <section className={styles.network}>
          <div className="container">
            <div className={styles.networkHeader}>
              <h2 className={styles.sectionTitle}>{networkTitle}</h2>
              {networkDesc && <div className={styles.richText} dangerouslySetInnerHTML={{ __html: networkDesc }} />}
              <Link href="/sell-with-us" className="btn btn-secondary">
                Sell With Us <ArrowRight />
              </Link>
            </div>
            {networkCards.length > 0 && (
              <div className={styles.networkGrid}>
                {networkCards.map((item) => (
                  <Link key={item.id} href={item.url || '#'} className={styles.networkCard}>
                    {item.thumbnail && (
                      <img
                        src={item.thumbnail}
                        alt={item.title}
                        loading="lazy"
                        className={styles.networkImage}
                      />
                    )}
                    <div className={styles.networkOverlay}>
                      <h3>{item.title}</h3>
                    </div>
                  </Link>
                ))}
              </div>
            )}
          </div>
        </section>
      )}

      {advisors.length > 0 && (
        <>
          <section className={styles.advisorsHeader}>
            <div className="container">
              <h2 className={styles.advisorsTitle}>{advisorsTitle}</h2>
            </div>
          </section>
          <section className={styles.advisorsSection}>
            <div className="container">
              <div className={styles.advisorsGrid}>
                {advisors.map((advisor) => (
                  <Link key={advisor.id} href={advisor.url || '#'} className={styles.advisorCard}>
                    {advisor.thumbnail && (
                      <img
                        src={advisor.thumbnail}
                        alt={advisor.title}
                        loading="lazy"
                        className={styles.advisorImage}
                      />
                    )}
                    <div className={styles.advisorBody}>
                      <h3>{advisor.title}</h3>
                      {advisor.description && <p className={styles.advisorRole}>{advisor.description}</p>}
                      {advisor.field?.real_estate_broker_desc && (
                        <p className={styles.advisorSummary}>
                          {stripHtmlTags(advisor.field.real_estate_broker_desc).slice(0, 220)}...
                        </p>
                      )}
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          </section>
        </>
      )}

      {discoverCards.length > 0 && (
        <>
          <section className={styles.discoverHeader}>
            <div className="container">
              <h2 className={styles.sectionTitle}>Let’s Begin the Conversation.</h2>
            </div>
          </section>
          <section className={styles.discoverSection}>
            <div className="container">
              <div className={styles.discoverGrid}>
                {discoverCards.map((item) => (
                  <Link key={item.id} href={item.url || '#'} className={styles.discoverCard}>
                    {item.thumbnail && (
                      <img
                        src={item.thumbnail}
                        alt={item.title}
                        loading="lazy"
                        className={styles.discoverImage}
                      />
                    )}
                    <div className={styles.discoverBody}>
                      <h3>{item.title}</h3>
                      {item.description && <p>{item.description}</p>}
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          </section>
        </>
      )}
    </>
  );
}

export async function generateStaticParams() {
  const ids = new Set([
    ...getMenuRouteIds('/about/'),
    ...getNewsRouteIds('/about/'),
  ]);

  return Array.from(ids).map((routeId) => ({ id: routeId }));
}
