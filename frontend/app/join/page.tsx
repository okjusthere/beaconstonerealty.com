import Link from 'next/link';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { getGlobalData, getNewsDetail, getNewsList } from '@/lib/api';

const FORM_NOTE_HTML = `
  <p>Sending this form opens your email app with a prepared message to Beacon Stone Realty. By continuing, you acknowledge our <a href="/legal">Privacy Policy</a> and <a href="/legal">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>You can review and edit the draft before sending it from your own email account.</p>
`;

function ArrowRight() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

function normalizeText(value?: string): string {
  return value?.replace(/\u00a0/g, ' ').trim() || '';
}

export default async function JoinPage() {
  const id = '39';

  let heroTitle = 'Join Us';
  let heroDesc = '';
  let introTitle = '';
  let introContent = '';
  let introSideContent = '';
  let featureImage = '';
  let featureTitle = '';
  let featureContent = '';
  let careerTitle = '';
  let careerCards: Array<{ id: number; title: string; url: string; thumbnail: string; description: string }> = [];
  let discoverMore: Array<{ id: number; title: string; url: string; thumbnail: string; description: string }> = [];
  let joinFormTitle = '';
  let joinFormDescription = '';
  let recipientEmail = 'info@beacon-stone.com';
  let brokerPhotos: Array<{ id: number; title: string; thumbnail: string; url: string }> = [];

  try {
    const [globalData, introData, sideData, featureData, careerData, discoverData, joinFormData, brokersData] = await Promise.allSettled([
      getGlobalData(),
      getNewsDetail(40),
      getNewsDetail(41),
      getNewsDetail(42),
      getNewsList(7, -1, 7),
      getNewsList(8, -1, 8),
      getNewsDetail(52),
      getNewsList(6, -1, 6),
    ]);

    if (globalData.status === 'fulfilled') {
      recipientEmail = globalData.value.web_info.email || recipientEmail;
      const joinMenu = globalData.value.menu_info.find((item) => item.url === '/join' || item.url === `/joinUs/${id}`);
      if (joinMenu) {
        heroTitle = joinMenu.sub_title || joinMenu.title || heroTitle;
        heroDesc = joinMenu.remarks || heroDesc;
      }
      const joinClass = globalData.value.news_class_info.find((item) => item.id === 7);
      if (joinClass) {
        careerTitle = joinClass.title;
      }
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
    if (joinFormData.status === 'fulfilled') {
      joinFormTitle = joinFormData.value.title;
      joinFormDescription = joinFormData.value.description;
    }
    if (brokersData.status === 'fulfilled') {
      brokerPhotos = (brokersData.value as typeof brokerPhotos).filter((b) => b.thumbnail && !b.thumbnail.includes('no_picture'));
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
            <Link href="/sell-with-us" className={styles.btnOutline}>
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
                {featureImage && <img src={featureImage} alt={featureTitle || 'Join us'} loading="eager" />}
              </div>
              <div className={styles.featureContent}>
                <h2 className={styles.sectionTitle}>{featureTitle}</h2>
                <div className={styles.richContent} dangerouslySetInnerHTML={{ __html: featureContent }} />
              </div>
            </div>
          </div>
        </section>
      )}

      {brokerPhotos.length >= 3 && (
        <section className={styles.brokerFan}>
          <div className="container">
            <div className={styles.fanWrapper}>
              <div className={styles.fanCard} style={{ '--fan-index': 0 } as React.CSSProperties}>
                <img src={brokerPhotos[1]?.thumbnail} alt={brokerPhotos[1]?.title} />
              </div>
              <div className={`${styles.fanCard} ${styles.fanCardCenter}`} style={{ '--fan-index': 1 } as React.CSSProperties}>
                <img src={brokerPhotos[0]?.thumbnail} alt={brokerPhotos[0]?.title} />
              </div>
              <div className={styles.fanCard} style={{ '--fan-index': 2 } as React.CSSProperties}>
                <img src={brokerPhotos[2]?.thumbnail} alt={brokerPhotos[2]?.title} />
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
              {careerCards.map((item, index) => {
                const itemTitle = normalizeText(item.title);
                const itemDescription = normalizeText(item.description);
                const hasCopy = Boolean(itemTitle || itemDescription);

                return (
                  <article
                    key={item.id}
                    className={`${styles.careerCard} ${!hasCopy ? styles.careerCardVisualOnly : ''}`}
                  >
                    {item.thumbnail && (
                      <img
                        src={item.thumbnail}
                        alt={itemTitle || `Career spotlight ${index + 1}`}
                        loading="eager"
                        className={styles.careerImage}
                      />
                    )}
                    {hasCopy ? (
                      <div className={styles.careerBody}>
                        {itemTitle && <h3>{itemTitle}</h3>}
                        {itemDescription && <p>{itemDescription}</p>}
                      </div>
                    ) : (
                      <div className={styles.careerOverlay}>
                        <span>Beacon Stone Realty</span>
                      </div>
                    )}
                  </article>
                );
              })}
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
                    {item.thumbnail && <img src={item.thumbnail} alt={item.title} loading="eager" className={styles.discoverImage} />}
                    <div className={styles.discoverBody}>
                      <h3>{item.title}</h3>
                      {normalizeText(item.description) && <p>{item.description}</p>}
                    </div>
                  </Link>
                ))}
              </div>
            </div>
          </section>
        </>
      )}

      <section className={styles.leadSection}>
        <div className="container">
          <div className={styles.leadShell}>
            <LegacyLeadForm
              variant="join"
              submissionTitle="Join as an agent"
              eyebrow="Join Beacon Stone Realty"
              title={joinFormTitle || 'Give yourself every advantage'}
              description={
                joinFormDescription
                || 'Taking your business to the next level requires more opportunities and more wins. Introduce yourself, the market you focus on, and how you see your platform growing with Beacon Stone Realty.'
              }
              messagePlaceholder="Share your background and the kind of opportunities you want to build."
              noteHtml={FORM_NOTE_HTML}
              disclaimerHtml={FORM_DISCLAIMER_HTML}
              recipientEmail={recipientEmail}
              successMessage="Your email app has been opened with a recruiting inquiry draft."
            />
          </div>
        </div>
      </section>
    </>
  );
}
