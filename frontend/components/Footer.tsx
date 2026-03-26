import Link from 'next/link';
import styles from './Footer.module.css';
import type { WebInfo, MenuItem } from '@/lib/api';

interface FooterProps {
  webInfo: WebInfo;
  menuItems: MenuItem[];
}

const socialLinks = [
  { name: 'Instagram', url: '#' },
  { name: 'TikTok', url: '#' },
  { name: 'LinkedIn', url: '#' },
  { name: 'YouTube', url: '#' },
  { name: 'X', url: '#' },
  { name: 'Facebook', url: '#' },
  { name: 'Pinterest', url: '#' },
  { name: 'Red Note', url: '#' },
];

export default function Footer({ webInfo, menuItems }: FooterProps) {
  const currentYear = new Date().getFullYear();

  return (
    <footer className={styles.footer}>
      <div className={styles.container}>
        {/* Company Info */}
        <div className={styles.companySection}>
          <div className={styles.companyName}>{webInfo.company || 'Beacon Stone Realty'}</div>
          {webInfo.address && <p className={styles.companyDetail}>{webInfo.address}</p>}
          {webInfo.phone && (
            <p className={styles.companyDetail}>
              Phone: <a href={`tel:${webInfo.phone}`}>{webInfo.phone}</a>
            </p>
          )}
          {webInfo.email && (
            <p className={styles.companyDetail}>
              Email: <a href={`mailto:${webInfo.email}`}>{webInfo.email}</a>
            </p>
          )}
        </div>

        {/* Navigation Links */}
        <div className={styles.navSection}>
          <div className={styles.navGrid}>
            {menuItems.filter(m => m.is_show).slice(0, 8).map((item) => (
              <div key={item.id} className={styles.navColumn}>
                <Link href={item.url || '#'} className={styles.navTitle}>
                  {item.title}
                </Link>
                {item.children.length > 0 && (
                  <ul className={styles.subNavList}>
                    {item.children.filter(c => c.is_show).map((child) => (
                      <li key={child.id}>
                        <Link href={child.url || '#'} className={styles.subNavLink}>
                          {child.title}
                        </Link>
                      </li>
                    ))}
                  </ul>
                )}
              </div>
            ))}
          </div>
        </div>

        {/* Social Links */}
        <div className={styles.socialSection}>
          <ul className={styles.socialList}>
            {socialLinks.map((social) => (
              <li key={social.name}>
                <a
                  href={social.url}
                  target="_blank"
                  rel="noopener noreferrer"
                  className={styles.socialLink}
                >
                  {social.name}
                </a>
              </li>
            ))}
          </ul>
        </div>

        {/* Copyright */}
        <div className={styles.copyright}>
          <p>Copyright © 2022-{currentYear} {webInfo.company}. All Rights Reserved.</p>
        </div>
      </div>
    </footer>
  );
}
