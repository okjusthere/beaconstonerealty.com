import Link from 'next/link';
import styles from './page.module.css';
import { findMenuByPath, getGlobalData, getNewsList, type NewsItem } from '@/lib/api';

export const metadata = {
  title: 'Properties | Beaconstone Realty',
  description: 'Browse the latest Beacon Stone Realty developments and residential opportunities.',
};

type PropertyCard = Pick<NewsItem, 'id' | 'title' | 'url' | 'thumbnail' | 'keywords' | 'description' | 'content' | 'field'>;

export default async function PropertiesPage() {
  let eyebrow = 'Property Center';
  let heroTitle = 'Where Elevated Living Begins';
  let properties: PropertyCard[] = [];

  try {
    const [globalData, propertyList] = await Promise.allSettled([
      getGlobalData(),
      getNewsList(5, -1, 1),
    ]);

    if (globalData.status === 'fulfilled') {
      const menu = findMenuByPath(globalData.value.menu_info, '/properties');
      eyebrow = menu?.sub_title || menu?.title || eyebrow;
      heroTitle = menu?.remarks || heroTitle;
    }

    if (propertyList.status === 'fulfilled') {
      properties = propertyList.value as PropertyCard[];
    }
  } catch {
    // Preserve a readable empty state while legacy data finishes migrating.
  }

  return (
    <>
      <section className={styles.hero}>
        <div className="container">
          <div className={styles.heroInner}>
            <p className={styles.eyebrow}>{eyebrow}</p>
            <h1 className={styles.heroTitle}>{heroTitle}</h1>
          </div>
        </div>
      </section>

      <section className={styles.listing}>
        <div className="container">
          {properties.length > 0 ? (
            <ul className={styles.grid}>
              {properties.map((property) => (
                <li key={property.id} className={styles.gridItem}>
                  <article className={styles.card}>
                    <Link href={property.url || `/properties/${property.id}`} className={styles.imageLink}>
                      {property.thumbnail && (
                        <img
                          src={property.thumbnail}
                          alt={property.title}
                          className={styles.cardImage}
                          loading="lazy"
                        />
                      )}
                    </Link>
                    <div className={styles.cardBody}>
                      <Link href={property.url || `/properties/${property.id}`} className={styles.cardCopy}>
                        {property.keywords && <span className={styles.cardEyebrow}>{property.keywords}</span>}
                        <h2 className={styles.cardTitle}>{property.title}</h2>
                        {property.description && (
                          <p className={styles.cardPrice}><strong>Starting Price:</strong>{property.description}</p>
                        )}
                        <div className={styles.cardMeta}>
                          {property.field?.total_residences && (
                            <span className={styles.cardResidences}>
                              {property.field.total_residences} Total Residences
                            </span>
                          )}
                          {property.content && (
                            <div
                              className={styles.cardMarketing}
                              dangerouslySetInnerHTML={{ __html: property.content }}
                            />
                          )}
                        </div>
                      </Link>
                    </div>
                  </article>
                </li>
              ))}
            </ul>
          ) : (
            <div className={styles.empty}>
              <p>No properties found. Please check back later.</p>
            </div>
          )}
        </div>
      </section>
    </>
  );
}
