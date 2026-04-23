import { urlFor } from '@/sanity/client';

export type NewsArticleAuthor = {
  name?: string;
  slug?: { current?: string };
  title?: string;
  photo?: unknown;
};

export type NewsArticle = {
  _id: string;
  legacyId?: number;
  title: string;
  slug: string;
  excerpt: string;
  coverImage?: unknown;
  coverImageAlt: string;
  publishedAt: string;
  featured: boolean;
  seoTitle: string;
  seoDescription: string;
  body: unknown[];
  authorAgent?: NewsArticleAuthor;
};

function portableTextToPlainText(value: unknown): string {
  if (!Array.isArray(value)) {
    return '';
  }

  return value
    .map((block) => {
      if (!block || typeof block !== 'object' || (block as { _type?: string })._type !== 'block') {
        return '';
      }

      const children = (block as { children?: Array<{ text?: string }> }).children || [];
      return children.map((child) => child.text || '').join('');
    })
    .filter(Boolean)
    .join(' ')
    .replace(/\s+/g, ' ')
    .trim();
}

export function mapNewsArticle(raw: Record<string, unknown>): NewsArticle {
  const excerpt =
    typeof raw.excerpt === 'string' && raw.excerpt.trim()
      ? raw.excerpt.trim()
      : portableTextToPlainText(raw.body).slice(0, 220).trim();

  return {
    _id: typeof raw._id === 'string' ? raw._id : '',
    legacyId: typeof raw.legacyId === 'number' ? raw.legacyId : undefined,
    title: typeof raw.title === 'string' ? raw.title : '',
    slug:
      typeof raw.slug === 'object' && raw.slug && typeof (raw.slug as { current?: unknown }).current === 'string'
        ? (raw.slug as { current: string }).current
        : '',
    excerpt,
    coverImage: raw.coverImage,
    coverImageAlt:
      typeof raw.coverImageAlt === 'string' && raw.coverImageAlt.trim()
        ? raw.coverImageAlt.trim()
        : typeof raw.title === 'string'
          ? raw.title
          : 'Real Estate News',
    publishedAt: typeof raw.publishedAt === 'string' ? raw.publishedAt : '',
    featured: Boolean(raw.featured),
    seoTitle: typeof raw.seoTitle === 'string' ? raw.seoTitle : '',
    seoDescription: typeof raw.seoDescription === 'string' ? raw.seoDescription : '',
    body: Array.isArray(raw.body) ? raw.body : [],
    authorAgent:
      raw.authorAgent && typeof raw.authorAgent === 'object'
        ? (raw.authorAgent as NewsArticleAuthor)
        : undefined,
  };
}

export function resolveNewsArticleHref(article: Pick<NewsArticle, 'slug' | 'legacyId'>): string {
  if (article.slug) {
    return `/news/${article.slug}`;
  }

  if (article.legacyId) {
    return `/news/${article.legacyId}`;
  }

  return '/news';
}

export function resolveNewsImageUrl(source: unknown): string {
  if (!source) {
    return '';
  }

  try {
    return urlFor(source).width(1600).fit('max').auto('format').url();
  } catch {
    return '';
  }
}

export function formatNewsDate(value: string): string {
  if (!value) {
    return '';
  }

  try {
    return new Intl.DateTimeFormat('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
    }).format(new Date(value));
  } catch {
    return '';
  }
}
