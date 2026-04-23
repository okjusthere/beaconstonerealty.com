import { PortableText, type PortableTextComponents } from '@portabletext/react';
import { urlFor } from '@/sanity/client';

const components: PortableTextComponents = {
  types: {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    image: ({ value }: { value: any }) => {
      const imageUrl = value ? urlFor(value).width(1400).fit('max').auto('format').url() : '';
      if (!imageUrl) return null;

      return (
        <figure>
          <img src={imageUrl} alt={value.alt || ''} loading="lazy" />
          {value.caption ? <figcaption>{value.caption}</figcaption> : null}
        </figure>
      );
    },
  },
  block: {
    h2: ({ children }) => <h2>{children}</h2>,
    h3: ({ children }) => <h3>{children}</h3>,
    blockquote: ({ children }) => <blockquote>{children}</blockquote>,
  },
  marks: {
    link: ({ children, value }) => {
      const href = value?.href || '#';
      const external = /^https?:\/\//i.test(href);
      return (
        <a
          href={href}
          target={external ? '_blank' : undefined}
          rel={external ? 'noopener noreferrer' : undefined}
        >
          {children}
        </a>
      );
    },
  },
};

export default function PortableTextContent({
  value,
  className,
}: {
  value: unknown[];
  className?: string;
}) {
  if (!value?.length) {
    return null;
  }

  return (
    <div className={className}>
      <PortableText value={value as never} components={components} />
    </div>
  );
}
