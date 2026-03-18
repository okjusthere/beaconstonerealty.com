import styles from './page.module.css';
import Link from 'next/link';
import { getGlobalData, getNewsDetail, getNewsList, getPicByClassId } from '@/lib/api';

// Arrow icon component
function ArrowRight() {
  return (
    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
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
    const [heroData, aboutData, storyData, exclusiveData, bannerData, globalData, propertyList] = await Promise.allSettled([
      getNewsDetail(11),
      getNewsDetail(1),
      getNewsDetail(2),
      getNewsDetail(3),
      getNewsDetail(39),
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
    if (bannerData.status === 'fulfilled') {
      // Banner video HTML is in content
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
      {/* ========== Hero Section with Video ========== */}
      <section className={styles.hero}>
        <div className={styles.heroVideo}>
          <video
            autoPlay
            muted
            loop
            playsInline
            poster="/video/hero-poster.jpg"
            className={styles.heroVideoElement}
          >
            <source src="/video/hero-1080p.mp4" type="video/mp4" />
          </video>
          <div className={styles.heroOverlay} />
        </div>
        <div className={styles.heroContent}>
          <h1 className={styles.heroTitle}>{heroTitle}</h1>
          <div className={styles.heroSearch}>
            <Link href="/properties" className={`btn btn-secondary ${styles.heroBtn}`}>
              Explore Properties <ArrowRight />
            </Link>
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
                <Link href="/about/13" className="btn-arrow">
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
            <p className={styles.exclusiveDesc}>{exclusiveDesc}</p>
            <Link href="/sell-with-us" className={`btn btn-secondary ${styles.exclusiveBtn}`}>
              Sell With Us <ArrowRight />
            </Link>
          </div>
        </div>
      </section>

      {/* ========== Video Section ========== */}
      {videoContent && (
        <section className={`section ${styles.videoSection}`}>
          <div className="container">
            <div
              className={styles.videoWrapper}
              dangerouslySetInnerHTML={{ __html: videoContent }}
            />
          </div>
        </section>
      )}

      {/* ========== Properties / Exceptional Locations ========== */}
      <section className={`section-lg ${styles.properties}`}>
        <div className="container">
          <div className={styles.propertiesHeader}>
            <h2 className={styles.sectionTitle}>{propertySectionTitle || 'Exceptional Locations'}</h2>
            <p className={styles.propertiesDesc}>{propertySectionDesc}</p>
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
                    {property.field?.house_introduction || property.description}
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
