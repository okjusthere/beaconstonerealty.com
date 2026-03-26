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

const navLinks = [
  { name: 'Home', href: '/' },
  { name: 'About Us', href: '/about/13' },
  { name: 'Property Center', href: '/properties' },
  { name: 'Real Estate Advisors', href: '/brokers' },
  { name: 'Join Us', href: '/join' },
  { name: 'Contact Us', href: '/contact' },
];

export default function Footer({ webInfo }: FooterProps) {
  const currentYear = new Date().getFullYear();

  return (
    <footer className={styles.footer}>
      <div className={styles.container}>
        {/* Nav Links */}
        <nav className={styles.navSection}>
          <ul className={styles.navList}>
            {navLinks.map((link) => (
              <li key={link.name}>
                <a href={link.href} className={styles.navLink}>{link.name}</a>
              </li>
            ))}
          </ul>
        </nav>

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
          <p>Copyright &copy; 2022-{currentYear} {webInfo.company || 'Beacon Stone Realty'} Rights Reserved.</p>
        </div>

        {/* Compliance Disclaimers */}
        <div className={styles.compliance}>
          <p><a href="https://www.trec.texas.gov/forms/consumer-protection-notice" target="_blank" rel="noopener noreferrer">Texas Real Estate Commission Consumer Protection Notice</a></p>
          <p>Beacon Stone Realty fully supports the principles of the Fair Housing Act and the Equal Opportunity Act. We are committed to providing equal housing opportunities without discrimination.</p>
          <p>The information contained on this website is deemed reliable but is not guaranteed. Listings and property information may be subject to errors, omissions, changes in price, prior sale, or withdrawal without notice.</p>
          <p>All property information, including square footage and dimensions, is approximate and should be independently verified.</p>
        </div>
      </div>
    </footer>
  );
}
