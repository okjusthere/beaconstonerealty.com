import Link from 'next/link';
import { notFound } from 'next/navigation';
import PortableTextContent from '@/components/PortableTextContent';
import {
  getAllNewsArticles,
  getNewsArticleByIdentifier,
  getNewsArticleRouteParams,
} from '@/sanity/fetch';
import {
  formatNewsDate,
  mapNewsArticle,
  resolveNewsArticleHref,
  resolveNewsImageUrl,
} from '@/lib/news';
import styles from './page.module.css';

export async function generateStaticParams() {
  const routeParams = await getNewsArticleRouteParams();
  const params = new Set<string>();

  for (const item of routeParams || []) {
    if (item.slug) params.add(item.slug);
    if (item.legacyId) params.add(item.legacyId);
  }

  return Array.from(params).map((slug) => ({ slug }));
}

export async function generateMetadata({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const rawArticle = await getNewsArticleByIdentifier(slug);

  if (!rawArticle) {
    return {
      title: 'Real Estate News | Beacon Stone Realty',
    };
  }

  const article = mapNewsArticle(rawArticle);

  return {
    title: article.seoTitle || `${article.title} | Beacon Stone Realty`,
    description: article.seoDescription || article.excerpt || 'Real Estate News from Beacon Stone Realty',
  };
}

export default async function NewsArticlePage({ params }: { params: Promise<{ slug: string }> }) {
  const { slug } = await params;
  const rawArticle = await getNewsArticleByIdentifier(slug);

  if (!rawArticle) {
    notFound();
  }

  const article = mapNewsArticle(rawArticle);
  const coverImageUrl = resolveNewsImageUrl(article.coverImage);

  const allNewsRaw = await getAllNewsArticles();
  const relatedArticles = (allNewsRaw || [])
    .map((item) => mapNewsArticle(item))
    .filter((item) => item._id !== article._id)
    .slice(0, 3);

  return (
    <article className={styles.page}>
      <div className="container">
        <div className={styles.header}>
          <Link href="/news" className={styles.backLink}>
            Real Estate News
          </Link>
          {article.publishedAt ? (
            <p className={styles.meta}>{formatNewsDate(article.publishedAt)}</p>
          ) : null}
          <h1 className={styles.title}>{article.title}</h1>
          {article.excerpt ? <p className={styles.excerpt}>{article.excerpt}</p> : null}
        </div>

        {coverImageUrl ? (
          <div className={styles.heroImageWrap}>
            <img src={coverImageUrl} alt={article.coverImageAlt} className={styles.heroImage} />
          </div>
        ) : null}

        <div className={styles.contentWrap}>
          <PortableTextContent value={article.body} className={styles.content} />
        </div>

        {relatedArticles.length > 0 ? (
          <section className={styles.relatedSection}>
            <div className={styles.relatedHeader}>
              <p className={styles.relatedEyebrow}>Continue Reading</p>
              <h2 className={styles.relatedTitle}>More from Real Estate News</h2>
            </div>
            <div className={styles.relatedGrid}>
              {relatedArticles.map((item) => {
                const href = resolveNewsArticleHref(item);
                const imageUrl = resolveNewsImageUrl(item.coverImage);
                return (
                  <article key={item._id} className={styles.relatedCard}>
                    <Link href={href} className={styles.relatedImageLink}>
                      {imageUrl ? (
                        <img src={imageUrl} alt={item.coverImageAlt} className={styles.relatedImage} loading="lazy" />
                      ) : (
                        <div className={styles.relatedFallback}>Beacon Stone Realty</div>
                      )}
                    </Link>
                    <div className={styles.relatedBody}>
                      {item.publishedAt ? <p className={styles.relatedDate}>{formatNewsDate(item.publishedAt)}</p> : null}
                      <h3 className={styles.relatedCardTitle}>
                        <Link href={href}>{item.title}</Link>
                      </h3>
                    </div>
                  </article>
                );
              })}
            </div>
          </section>
        ) : null}
      </div>
    </article>
  );
}
