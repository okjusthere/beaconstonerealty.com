import styles from './Footer.module.css';
import type { WebInfo } from '@/lib/api';

export type FooterSocialLinks = Partial<{
  instagram: string;
  tiktok: string;
  linkedin: string;
  youtube: string;
  x: string;
  facebook: string;
  pinterest: string;
  rednote: string;
}>;

interface FooterProps {
  webInfo: WebInfo;
  socialLinks?: FooterSocialLinks | null;
}

const socialLinkConfig: Array<{ name: string; key: keyof FooterSocialLinks }> = [
  { name: 'Instagram', key: 'instagram' },
  { name: 'TikTok', key: 'tiktok' },
  { name: 'LinkedIn', key: 'linkedin' },
  { name: 'YouTube', key: 'youtube' },
  { name: 'X', key: 'x' },
  { name: 'Facebook', key: 'facebook' },
  { name: 'Pinterest', key: 'pinterest' },
  { name: 'Red Note', key: 'rednote' },
];

export default function Footer({ webInfo, socialLinks }: FooterProps) {
  const currentYear = new Date().getFullYear();
  const resolvedSocialLinks = socialLinkConfig
    .map((social) => ({
      name: social.name,
      url: socialLinks?.[social.key]?.trim() || '',
    }))
    .filter((social) => social.url);

  return (
    <footer className={styles.footer}>
      <div className={styles.container}>
        {resolvedSocialLinks.length > 0 ? (
          <div className={styles.socialSection}>
            <ul className={styles.socialList}>
              {resolvedSocialLinks.map((social) => (
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
        ) : null}

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
