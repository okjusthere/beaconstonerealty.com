import Link from 'next/link';

export const metadata = {
  title: '404 - Page Not Found | Beaconstone Realty',
};

export default function NotFound() {
  return (
    <div style={{
      minHeight: '60vh',
      display: 'flex',
      flexDirection: 'column' as const,
      alignItems: 'center',
      justifyContent: 'center',
      textAlign: 'center',
      padding: 'var(--space-xl)',
      background: 'var(--color-off-white)',
    }}>
      <h1 style={{
        fontFamily: 'var(--font-heading)',
        fontSize: 'clamp(4rem, 10vw, 8rem)',
        color: 'var(--color-navy)',
        lineHeight: '1',
        marginBottom: 'var(--space-md)',
      }}>
        404
      </h1>
      <p style={{
        fontSize: '1.25rem',
        color: 'var(--color-grey-dark)',
        marginBottom: 'var(--space-xl)',
        maxWidth: '400px',
      }}>
        The page you&apos;re looking for doesn&apos;t exist or has been moved.
      </p>
      <Link href="/" className="btn btn-primary">
        Return Home
      </Link>
    </div>
  );
}
