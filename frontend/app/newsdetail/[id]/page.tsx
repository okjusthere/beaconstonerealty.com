import { notFound } from 'next/navigation';
import { getNewsDetail, getNewsRouteIds } from '@/lib/api';
import styles from './page.module.css';

export async function generateStaticParams() {
  return getNewsRouteIds('/newsdetail/').map((id) => ({ id }));
}

export default async function NewsDetailPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const articleId = Number(id);

  if (!Number.isFinite(articleId)) {
    notFound();
  }

  let article;
  try {
    article = await getNewsDetail(articleId);
  } catch {
    notFound();
  }

  return (
    <section className={styles.page}>
      <div className="container">
        {article.thumbnail && (
          <div className={styles.hero}>
            <img src={article.thumbnail} alt={article.title} className={styles.heroImage} />
          </div>
        )}
        <article className={styles.inner}>
          <p className={styles.eyebrow}>News & Insights</p>
          <h1 className={styles.title}>{article.title}</h1>
          {article.description && <p className={styles.description}>{article.description}</p>}
          {article.content && (
            <div
              className={styles.content}
              dangerouslySetInnerHTML={{ __html: article.content }}
            />
          )}
        </article>
      </div>
    </section>
  );
}
