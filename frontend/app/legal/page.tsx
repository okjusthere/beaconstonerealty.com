import styles from './page.module.css';
import { getNewsDetail } from '@/lib/api';

export default async function LegalPage() {
  let title = 'Beacon Stone Realty';
  let description = '';
  let content = '';

  try {
    const article = await getNewsDetail(61);
    title = article.title || title;
    description = article.description || '';
    content = article.content || '';
  } catch {
    // Keep a readable fallback if the legal page cannot be loaded.
  }

  return (
    <section className={styles.page}>
      <div className="container">
        <div className={styles.inner}>
          <p className={styles.eyebrow}>Legal</p>
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
