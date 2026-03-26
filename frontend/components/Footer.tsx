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

export default function Footer({ webInfo }: FooterProps) {
  const currentYear = new Date().getFullYear();

  return (
    <footer className={styles.footer}>
      <div className={styles.container}>
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
          <p>Copyright &copy; 2022-{currentYear} {webInfo.company || 'Beacon Stone Realty'}. All Rights Reserved.</p>
        </div>
      </div>
    </footer>
  );
}
