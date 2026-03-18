import styles from './page.module.css';
import Link from 'next/link';
import { getNewsList, getGlobalData, getInnerNews } from '@/lib/api';

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7"/>
    </svg>
  );
}

export const metadata = {
  title: 'Properties | Beaconstone Realty',
  description: 'Browse our exclusive collection of luxury properties and exceptional locations.',
};

export default async function PropertiesPage() {
  let properties: Array<{
    id: number; title: string; url: string; thumbnail: string;
    description: string; field?: Record<string, string>;
  }> = [];
  let pageTitle = 'Our Properties';
  let pageDesc = '';

  try {
    const [globalData, propertyList] = await Promise.allSettled([
      getGlobalData(),
      getNewsList(5, -1, 1),
    ]);

    if (globalData.status === 'fulfilled') {
      const nc = globalData.value.news_class_info.find(c => c.id === 1);
      if (nc) {
        pageTitle = nc.title || pageTitle;
        pageDesc = nc.description || '';
      }
    }
    if (propertyList.status === 'fulfilled') {
      properties = propertyList.value as typeof properties;
    }
  } catch {}

  return (
    <>
      {/* Hero */}
      <section className={styles.hero}>
        <div className="container">
          <span className={styles.eyebrow}>Beaconstone Realty</span>
          <h1 className={styles.heroTitle}>{pageTitle}</h1>
          {pageDesc && <p className={styles.heroDesc}>{pageDesc}</p>}
        </div>
      </section>

      {/* Property Grid */}
      <section className={`section-lg ${styles.listing}`}>
        <div className="container">
          <div className={styles.grid}>
            {properties.map((property) => (
              <Link
                key={property.id}
                href={property.url || `/properties/${property.id}`}
                className={styles.card}
              >
                <div className={styles.cardImageWrap}>
                  {property.thumbnail && (
                    <img
                      src={property.thumbnail}
                      alt={property.title}
                      loading="lazy"
                      className={styles.cardImage}
                    />
                  )}
                </div>
                <div className={styles.cardBody}>
                  <h3 className={styles.cardTitle}>{property.title}</h3>
                  <p className={styles.cardDesc}>
                    {property.field?.house_introduction || property.description}
                  </p>
                </div>
              </Link>
            ))}
          </div>

          {properties.length === 0 && (
            <div className={styles.empty}>
              <p>No properties found. Please check back later.</p>
            </div>
          )}
        </div>
      </section>
    </>
  );
}
