import styles from './page.module.css';
import Link from 'next/link';
import { getGlobalData, getMenuRouteIds, getNewsDetail, getNewsList, getNewsRouteIds } from '@/lib/api';

function ArrowRight() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
}

export default async function AboutPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let heroTitle = '';
  let heroDesc = '';
  let stats: Array<{ id: number; title: string; keywords: string; description: string }> = [];
  let mainTitle = '';
  let mainContent = '';
  let featureImage = '';
  let featureTitle = '';
  let featureContent = '';
  let ctaTitle = '';
  let ctaDesc = '';
  let teamMembers: Array<{ id: number; title: string; url: string; thumbnail: string; description: string }> = [];

  try {
    const [globalData, aboutHero, aboutStats, aboutMain, aboutFeature, team] = await Promise.allSettled([
      getGlobalData(),
      getNewsDetail(13),
      getNewsList(2, -1, 2),
      getNewsDetail(18),
      getNewsDetail(19),
      getNewsList(10, 2, 10),
    ]);

    if (globalData.status === 'fulfilled') {
      const aboutMenu = globalData.value.menu_info.find((item) => item.url === `/about/${id}`);
      if (aboutMenu) {
        heroTitle = aboutMenu.sub_title || aboutMenu.title;
        heroDesc = aboutMenu.remarks || '';
      }

      const ctaClass = globalData.value.news_class_info.find((item) => item.id === 3);
      if (ctaClass) {
        ctaTitle = ctaClass.title;
        ctaDesc = ctaClass.description;
      }
    }
    if (aboutHero.status === 'fulfilled') {
      heroTitle ||= aboutHero.value.title;
      heroDesc ||= aboutHero.value.description;
    }
    if (aboutStats.status === 'fulfilled') {
      stats = aboutStats.value as typeof stats;
    }
    if (aboutMain.status === 'fulfilled') {
      mainTitle = aboutMain.value.title;
      mainContent = aboutMain.value.description;
    }
    if (aboutFeature.status === 'fulfilled') {
      featureImage = aboutFeature.value.thumbnail;
      featureTitle = aboutFeature.value.title;
      featureContent = aboutFeature.value.description;
    }
    if (team.status === 'fulfilled') {
      teamMembers = team.value as typeof teamMembers;
    }
  } catch {
    // fallback
  }

  return (
    <>
      {/* Hero */}
      <section className={styles.hero}>
        <div className={styles.heroInner}>
          <h1 className={styles.heroTitle}>{heroTitle || 'About Us'}</h1>
          <p className={styles.heroDesc}>{heroDesc}</p>
        </div>
      </section>

      {/* Stats */}
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

      {/* Main Content */}
      <section className={`section ${styles.mainContent}`}>
        <div className="container">
          <h2 className={styles.sectionTitle}>{mainTitle}</h2>
          <div className={styles.richText} dangerouslySetInnerHTML={{ __html: mainContent }} />
        </div>
      </section>

      {/* Feature Image + Text */}
      {featureTitle && (
        <section className={styles.feature}>
          <div className={styles.featureMedia}>
            {featureImage && <img src={featureImage} alt={featureTitle} loading="lazy" />}
          </div>
          <div className={styles.featureText}>
            <div className="container">
              <h2>{featureTitle}</h2>
              <div dangerouslySetInnerHTML={{ __html: featureContent }} />
            </div>
          </div>
        </section>
      )}

      {/* CTA */}
      {ctaTitle && (
        <section className={`section ${styles.cta}`}>
          <div className="container" style={{ textAlign: 'center' }}>
            <h2 className={styles.sectionTitle}>{ctaTitle}</h2>
            <div className={styles.richText} dangerouslySetInnerHTML={{ __html: ctaDesc }} />
            <Link href="/sell-with-us" className="btn btn-secondary">
              Sell With Us <ArrowRight />
            </Link>
          </div>
        </section>
      )}

      {/* Team */}
      {teamMembers.length > 0 && (
        <section className={styles.team}>
          <div className="container">
            <h2 className={styles.sectionTitle} style={{ textAlign: 'center', marginBottom: 'var(--space-3xl)' }}>
              {"Let's Begin the Conversation."}
            </h2>
            <div className={styles.teamGrid}>
              {teamMembers.map((member) => (
                <Link key={member.id} href={member.url || '#'} className={styles.teamCard}>
                  <div className={styles.teamImageWrap}>
                    {member.thumbnail && (
                      <img src={member.thumbnail} alt={member.title} loading="lazy" className={styles.teamImage} />
                    )}
                    <div className={styles.teamOverlay}>
                      <h3 className={styles.teamName}>{member.title}</h3>
                      <p className={styles.teamRole}>{member.description}</p>
                    </div>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  );
}

export async function generateStaticParams() {
  const ids = new Set([
    ...getMenuRouteIds('/about/'),
    ...getNewsRouteIds('/about/'),
  ]);

  return Array.from(ids).map((id) => ({ id }));
}
