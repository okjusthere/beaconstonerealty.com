import Link from 'next/link';
import styles from './page.module.css';
import { getGlobalData, getMenuRouteIds, getNewsDetail, getNewsList, getNewsRouteIds } from '@/lib/api';

function ArrowRight() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

export default async function JoinUsPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let heroTitle = 'Join Us';
  let heroDesc = '';
  let heroMedia = '';
  let introTitle = '';
  let introContent = '';
  let introSideContent = '';
  let featureImage = '';
  let featureTitle = '';
  let featureContent = '';
  let careerTitle = '';
  let careerCards: Array<{ id: number; title: string; url: string; thumbnail: string; description: string }> = [];
  let discoverMore: Array<{ id: number; title: string; url: string; thumbnail: string; description: string }> = [];

  try {
    const [globalData, heroData, introData, sideData, featureData, careerData, discoverData] = await Promise.allSettled([
      getGlobalData(),
      getNewsDetail(39),
      getNewsDetail(40),
      getNewsDetail(41),
      getNewsDetail(42),
      getNewsList(7, -1, 7),
      getNewsList(8, -1, 8),
    ]);

    if (globalData.status === 'fulfilled') {
      const joinMenu = globalData.value.menu_info.find((item) => item.url === `/joinUs/${id}`);
      if (joinMenu) {
        heroTitle = joinMenu.sub_title || joinMenu.title || heroTitle;
        heroDesc = joinMenu.remarks || heroDesc;
      }
      const joinClass = globalData.value.news_class_info.find((item) => item.id === 7);
      if (joinClass) {
        careerTitle = joinClass.title;
      }
    }

    if (heroData.status === 'fulfilled') {
      heroMedia = heroData.value.content;
    }
    if (introData.status === 'fulfilled') {
      introTitle = introData.value.title;
      introContent = introData.value.content;
    }
    if (sideData.status === 'fulfilled') {
      introSideContent = sideData.value.content;
    }
    if (featureData.status === 'fulfilled') {
      featureImage = featureData.value.thumbnail;
      featureTitle = featureData.value.title;
      featureContent = featureData.value.content;
    }
    if (careerData.status === 'fulfilled') {
      careerCards = careerData.value as typeof careerCards;
    }
    if (discoverData.status === 'fulfilled') {
      discoverMore = discoverData.value as typeof discoverMore;
    }
  } catch {
    // Keep fallbacks for the initial migration pass.
  }

  return (
    <>
      <section className={styles.hero}>
        <div className={styles.heroInner}>
          <div className="container">
            <h1 className={styles.heroTitle}>{heroTitle}</h1>
            {heroDesc && <p className={styles.heroDesc}>{heroDesc}</p>}
          </div>
        </div>
        {heroMedia && (
          <div className={`container ${styles.heroMedia}`}>
            <div dangerouslySetInnerHTML={{ __html: heroMedia }} />
          </div>
        )}
      </section>

      <section className={`section-lg ${styles.intro}`}>
        <div className="container">
          <div className={styles.introGrid}>
            <div>
              <h2 className={styles.sectionTitle}>{introTitle}</h2>
              <div className={styles.richContent} dangerouslySetInnerHTML={{ __html: introContent }} />
            </div>
            <div className={styles.sidePanel} dangerouslySetInnerHTML={{ __html: introSideContent }} />
          </div>
          <div className={styles.introCta}>
            <Link href="/sell-with-us" className="btn btn-secondary">
              Join Us <ArrowRight />
            </Link>
          </div>
        </div>
      </section>

      {(featureImage || featureTitle || featureContent) && (
        <section className={`section-lg ${styles.feature}`}>
          <div className="container">
            <div className={styles.featureGrid}>
              <div className={styles.featureImageWrap}>
                {featureImage && <img src={featureImage} alt={featureTitle || 'Join us'} loading="lazy" />}
              </div>
              <div className={styles.featureContent}>
                <h2 className={styles.sectionTitle}>{featureTitle}</h2>
                <div className={styles.richContent} dangerouslySetInnerHTML={{ __html: featureContent }} />
              </div>
            </div>
          </div>
        </section>
      )}

      {careerCards.length > 0 && (
        <section className={`section-lg ${styles.careers}`}>
          <div className="container">
            <div className={styles.sectionHeader}>
              <h2 className={styles.sectionTitle}>{careerTitle || 'Unlock your potential'}</h2>
            </div>
            <div className={styles.careerGrid}>
              {careerCards.map((item) => (
                <Link key={item.id} href={item.url || '#'} className={styles.careerCard}>
                  {item.thumbnail && <img src={item.thumbnail} alt={item.title} loading="lazy" className={styles.careerImage} />}
                  <div className={styles.careerBody}>
                    <h3>{item.title}</h3>
                    <p>{item.description}</p>
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}

      {discoverMore.length > 0 && (
        <>
          <section className={styles.discoverHeader}>
            <div className="container">
              <h2 className={styles.sectionTitle}>Discover More</h2>
            </div>
          </section>
          <section className={styles.discoverSection}>
            <div className="container">
              <div className={styles.discoverGrid}>
                {discoverMore.map((item) => (
                  <Link key={item.id} href={item.url || '#'} className={styles.discoverCard}>
                    {item.thumbnail && <img src={item.thumbnail} alt={item.title} loading="lazy" className={styles.discoverImage} />}
                    <div className={styles.discoverBody}>
                      <h3>{item.title}</h3>
                      <p>{item.description}</p>
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
    ...getMenuRouteIds('/joinUs/'),
    ...getNewsRouteIds('/joinUs/'),
  ]);

  return Array.from(ids).map((id) => ({ id }));
}
