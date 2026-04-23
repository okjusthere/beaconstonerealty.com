import Link from 'next/link';
import { getAllNewsArticles } from '@/sanity/fetch';
import {
  formatNewsDate,
  mapNewsArticle,
  resolveNewsArticleHref,
  resolveNewsImageUrl,
} from '@/lib/news';
import styles from './page.module.css';

export const metadata = {
  title: 'Real Estate News | Beacon Stone Realty',
  description: 'Market intelligence, architecture, design, and editorial perspectives from Beacon Stone Realty.',
};

function ArrowRight() {
  return (
    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2">
      <path d="M5 12h14M12 5l7 7-7 7" />
    </svg>
  );
}

export default async function NewsPage() {
  const rawArticles = await getAllNewsArticles();
  const articles = (rawArticles || [])
    .map((article) => mapNewsArticle(article))
    .filter((article) => article.title);

  return (
    <section className={styles.page}>
      <div className="container">
        <div className={styles.hero}>
          <p className={styles.eyebrow}>Editorial</p>
          <h1 className={styles.title}>Real Estate News</h1>
          <p className={styles.description}>
            Market perspective, design signals, and residential intelligence curated through the lens of Beacon Stone Realty.
          </p>
        </div>

        {articles.length > 0 ? (
          <div className={styles.grid}>
            {articles.map((article, index) => {
              const imageUrl = resolveNewsImageUrl(article.coverImage);
              const href = resolveNewsArticleHref(article);

              return (
                <article
                  key={article._id}
                  className={`${styles.card} ${index === 0 ? styles.featuredCard : ''}`}
                >
                  <Link href={href} className={styles.cardImageLink}>
                    {imageUrl ? (
                      <img
                        src={imageUrl}
                        alt={article.coverImageAlt}
                        className={styles.cardImage}
                        loading={index === 0 ? 'eager' : 'lazy'}
                      />
                    ) : (
                      <div className={styles.imageFallback}>Beacon Stone Realty</div>
                    )}
                  </Link>
                  <div className={styles.cardBody}>
                    {article.publishedAt ? (
                      <p className={styles.cardDate}>{formatNewsDate(article.publishedAt)}</p>
                    ) : null}
                    <h2 className={styles.cardTitle}>
                      <Link href={href}>{article.title}</Link>
                    </h2>
                    {article.excerpt ? (
                      <p className={styles.cardExcerpt}>{article.excerpt}</p>
                    ) : null}
                    <Link href={href} className={styles.cardLink}>
                      Read Article <ArrowRight />
                    </Link>
                  </div>
                </article>
              );
            })}
          </div>
        ) : (
          <div className={styles.empty}>
            <p>No real estate news has been published yet.</p>
          </div>
        )}
      </div>
    </section>
  );
}
