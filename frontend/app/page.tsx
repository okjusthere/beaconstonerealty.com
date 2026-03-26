import Link from 'next/link';
import HeroVideo from '@/components/HeroVideo';
import { getGlobalData, getNewsDetail, getNewsList } from '@/lib/api';
import { BEACON_MUX_PLAYBACK_ID, BEACON_MUX_POSTER } from '@/lib/mux';
import styles from './page.module.css';

// Arrow icon component
function ArrowRight() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
}

function stripHtmlTags(html: string): string {
  return html
    .replace(/<br\s*\/?>/gi, ' ')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/gi, ' ')
    .replace(/\s+/g, ' ')
    .trim();
}

function renderHeroTitle(title: string) {
  if (title.trim() === 'Every home tells a story. Let yours begin here') {
    return (
      <>
        Every home tells a story.
        <br />
        Let yours begin here
      </>
    );
  }

  return title;
}

export default async function HomePage() {
  // Fetch all homepage data in parallel
  let heroTitle = 'Discover Exceptional Living';
  let aboutTitle = '';
  let aboutContent = '';
  let aboutImage = '';
  let storyTitle = '';
  let storyContent = '';
  let storyImage = '';
  let exclusiveTitle = '';
  let exclusiveDesc = '';
  let videoContent = '';
  let propertySectionTitle = '';
  let propertySectionDesc = '';
  let properties: Array<{
    id: number;
    title: string;
    url: string;
    thumbnail: string;
    description: string;
    field?: Record<string, string>;
  }> = [];

  try {
    const [heroData, aboutData, storyData, exclusiveData, globalData, propertyList] = await Promise.allSettled([
      getNewsDetail(11),
      getNewsDetail(1),
      getNewsDetail(2),
      getNewsDetail(3),
      getGlobalData(),
      getNewsList(5, -1, 1),
    ]);

    if (heroData.status === 'fulfilled') heroTitle = heroData.value.title;
    if (aboutData.status === 'fulfilled') {
      aboutTitle = aboutData.value.title;
      aboutContent = aboutData.value.content;
      aboutImage = aboutData.value.thumbnail;
    }
    if (storyData.status === 'fulfilled') {
      storyTitle = storyData.value.title;
      storyContent = storyData.value.content;
      storyImage = storyData.value.thumbnail;
    }
    if (exclusiveData.status === 'fulfilled') {
      exclusiveTitle = exclusiveData.value.title;
      exclusiveDesc = exclusiveData.value.description;
      videoContent = exclusiveData.value.content;
    }
    if (globalData.status === 'fulfilled') {
      const newsClass = globalData.value.news_class_info.find(c => c.id === 1);
      if (newsClass) {
        propertySectionTitle = newsClass.title;
        propertySectionDesc = newsClass.description;
      }
    }
    if (propertyList.status === 'fulfilled') {
      properties = propertyList.value as typeof properties;
    }
  } catch {
    // Use fallback values
  }

  return (
    <>
      {/* ========== Hero Intro + Banner Video ========== */}
      <section className={styles.hero}>
        <div className={styles.heroIntro}>
          <div className="container">
            <div className={styles.heroIntroGrid}>
              <div className={styles.heroIntroCopy}>
                <h1 className={styles.heroTitle}>{renderHeroTitle(heroTitle)}</h1>
              </div>
              <div className={styles.heroIntroAction}>
                <Link href="/properties" className={`btn ${styles.heroBtn}`}>
                  Explore Properties <ArrowRight />
                </Link>
              </div>
            </div>
          </div>
        </div>
        <div className={styles.heroMedia}>
          <div className={styles.heroMediaSurface}>
            <HeroVideo
              className={styles.heroVideoElement}
              playbackId={BEACON_MUX_PLAYBACK_ID}
              poster={BEACON_MUX_POSTER}
              title="Beacon Stone Realty showcase"
              muted
            />
          </div>
        </div>
      </section>

      {/* ========== About / Company Intro ========== */}
      <section className={`section ${styles.about}`}>
        <div className="container">
          <div className={styles.aboutGrid}>
            <div className={styles.aboutImage}>
              {aboutImage && (
                <img src={aboutImage} alt={aboutTitle || 'About us'} loading="lazy" />
              )}
            </div>
            <div className={styles.aboutText}>
              <h2 className={styles.sectionTitle}>{aboutTitle}</h2>
              <div
                className={styles.richContent}
                dangerouslySetInnerHTML={{ __html: aboutContent }}
              />
            </div>
          </div>
        </div>
      </section>

      {/* ========== Our Story ========== */}
      {storyTitle && (
        <section className={`section ${styles.story}`}>
          <div className="container">
            <div className={styles.storyInner}>
              <div className={styles.storyImage}>
                {storyImage && (
                  <img src={storyImage} alt={storyTitle} loading="lazy" />
                )}
              </div>
              <div className={styles.storyText}>
                <h2 className={styles.sectionTitle}>{storyTitle}</h2>
                <div
                  className={styles.richContent}
                  dangerouslySetInnerHTML={{ __html: storyContent }}
                />
                <Link href="/about" className="btn-arrow">
                  See Details <ArrowRight />
                </Link>
              </div>
            </div>
          </div>
        </section>
      )}

      {/* ========== Exclusive / Sell With Us ========== */}
      <section className={styles.exclusive}>
        <div className="container">
          <div className={styles.exclusiveInner}>
            <h2 className={styles.exclusiveTitle}>{exclusiveTitle}</h2>
            <div
              className={styles.exclusiveDesc}
              dangerouslySetInnerHTML={{ __html: exclusiveDesc }}
            />
            <div className={styles.exclusiveBtnWrap}>
              <Link href="/sell-with-us" className={`btn btn-secondary ${styles.exclusiveBtn}`}>
                Sell With Us <ArrowRight />
              </Link>
            </div>
          </div>
        </div>
      </section>

      {/* ========== Video Section ========== */}
      <section className={`section ${styles.videoSection}`}>
        <div className="container">
          <div className={styles.videoWrapper}>
            <HeroVideo
              className={styles.videoMux}
              playbackId={BEACON_MUX_PLAYBACK_ID}
              poster={BEACON_MUX_POSTER}
              title="Beacon Stone Realty Brand Film"
            />
          </div>
        </div>
      </section>

      {/* ========== Properties / Exceptional Locations ========== */}
      <section className={`section-lg ${styles.properties}`}>
        <div className="container">
          <div className={styles.propertiesHeader}>
            <h2
              className={styles.sectionTitle}
              dangerouslySetInnerHTML={{ __html: propertySectionTitle || 'Exceptional Locations' }}
            />
            <div
              className={styles.propertiesDesc}
              dangerouslySetInnerHTML={{ __html: propertySectionDesc }}
            />
            <Link href="/properties" className="btn-arrow">
              Explore More <ArrowRight />
            </Link>
          </div>
          <div className={styles.propertiesGrid}>
            {properties.slice(0, 6).map((property) => (
              <Link
                key={property.id}
                href={property.url || `/properties/${property.id}`}
                className={styles.propertyCard}
              >
                <div className={styles.propertyImageWrap}>
                  {property.thumbnail && (
                    <img
                      src={property.thumbnail}
                      alt={property.title}
                      loading="lazy"
                      className={styles.propertyImage}
                    />
                  )}
                </div>
                <div className={styles.propertyInfo}>
                  <h3 className={styles.propertyTitle}>{property.title}</h3>
                  <p className={styles.propertyDesc}>
                    {stripHtmlTags(property.field?.house_introduction || property.description)}
                  </p>
                </div>
              </Link>
            ))}
          </div>
        </div>
      </section>
    </>
  );
}
