import styles from './page.module.css';
import Link from 'next/link';
import { getNewsList } from '@/lib/api';

export const metadata = {
  title: 'News | Beaconstone Realty',
  description: 'Stay updated with the latest news and insights from Beaconstone Realty.',
};

export default async function NewsPage() {
  let articles: Array<{
    id: number; title: string; url: string; thumbnail: string;
    description: string; add_time: number;
  }> = [];

  try {
    const data = await getNewsList(4, -1, 8);
    articles = data as typeof articles;
  } catch {}

  return (
    <>
      <section className={styles.hero}>
        <div className="container">
          <h1 className={styles.heroTitle}>News & Insights</h1>
        </div>
      </section>

      <section className={`section-lg ${styles.listing}`}>
        <div className="container">
          <div className={styles.grid}>
            {articles.map((article) => (
              <Link key={article.id} href={article.url || '#'} className={styles.card}>
                <div className={styles.cardImage}>
                  {article.thumbnail && (
                    <img src={article.thumbnail} alt={article.title} loading="lazy" />
                  )}
                </div>
                <div className={styles.cardBody}>
                  <h3 className={styles.cardTitle}>{article.title}</h3>
                  <p className={styles.cardDesc}>{article.description}</p>
                </div>
              </Link>
            ))}
          </div>

          {articles.length === 0 && (
            <div className={styles.empty}>
              <p>No news articles found.</p>
            </div>
          )}
        </div>
      </section>
    </>
  );
}
