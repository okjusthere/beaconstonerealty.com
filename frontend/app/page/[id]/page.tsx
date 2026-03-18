import styles from './page.module.css';
import { getNewsDetail } from '@/lib/api';

export default async function LegacyContentPage({ params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;

  let title = 'Beacon Stone Realty';
  let description = '';
  let content = '';

  try {
    const article = await getNewsDetail(Number(id));
    title = article.title || title;
    description = article.description || '';
    content = article.content || '';
  } catch {
    // Keep a readable fallback if the legacy page cannot be loaded.
  }

  return (
    <section className={styles.page}>
      <div className="container">
        <div className={styles.inner}>
          <p className={styles.eyebrow}>Legacy Page</p>
          <h1 className={styles.title}>{title}</h1>
          {description && <p className={styles.description}>{description}</p>}
          {content && (
            <div
              className={styles.content}
              dangerouslySetInnerHTML={{ __html: content }}
            />
          )}
        </div>
      </div>
    </section>
  );
}
