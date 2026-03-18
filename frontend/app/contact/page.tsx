import styles from './page.module.css';
import { getGlobalData } from '@/lib/api';

export const metadata = {
  title: 'Contact Us | Beaconstone Realty',
  description: 'Get in touch with Beaconstone Realty. Reach out to our team for all your luxury real estate needs.',
};

export default async function ContactPage() {
  let webInfo = {
    company: 'Beaconstone Realty', address: '', phone: '', mobile: '',
    email: '', fax: '', contact: '', whatsapp: '',
  };

  try {
    const data = await getGlobalData();
    webInfo = data.web_info as typeof webInfo;
  } catch {}

  return (
    <>
      <section className={styles.hero}>
        <div className="container">
          <h1 className={styles.heroTitle}>Contact Us</h1>
          <p className={styles.heroDesc}>
            We&apos;d love to hear from you. Reach out to us anytime.
          </p>
        </div>
      </section>

      <section className={`section-lg ${styles.content}`}>
        <div className="container">
          <div className={styles.grid}>
            {/* Contact Info */}
            <div className={styles.info}>
              <h2 className={styles.infoTitle}>Get In Touch</h2>
              <div className={styles.infoList}>
                {webInfo.company && (
                  <div className={styles.infoItem}>
                    <span className={styles.infoLabel}>Company</span>
                    <span className={styles.infoValue}>{webInfo.company}</span>
                  </div>
                )}
                {webInfo.address && (
                  <div className={styles.infoItem}>
                    <span className={styles.infoLabel}>Address</span>
                    <span className={styles.infoValue}>{webInfo.address}</span>
                  </div>
                )}
                {webInfo.phone && (
                  <div className={styles.infoItem}>
                    <span className={styles.infoLabel}>Phone</span>
                    <a href={`tel:${webInfo.phone}`} className={styles.infoValue}>{webInfo.phone}</a>
                  </div>
                )}
                {webInfo.mobile && (
                  <div className={styles.infoItem}>
                    <span className={styles.infoLabel}>Mobile</span>
                    <a href={`tel:${webInfo.mobile}`} className={styles.infoValue}>{webInfo.mobile}</a>
                  </div>
                )}
                {webInfo.email && (
                  <div className={styles.infoItem}>
                    <span className={styles.infoLabel}>Email</span>
                    <a href={`mailto:${webInfo.email}`} className={styles.infoValue}>{webInfo.email}</a>
                  </div>
                )}
                {webInfo.whatsapp && (
                  <div className={styles.infoItem}>
                    <span className={styles.infoLabel}>WhatsApp</span>
                    <a href={`https://wa.me/${webInfo.whatsapp}`} className={styles.infoValue}>{webInfo.whatsapp}</a>
                  </div>
                )}
              </div>
            </div>

            {/* Map / Placeholder */}
            <div className={styles.mapWrap}>
              <div className={styles.mapPlaceholder}>
                <p>Map will be displayed here</p>
              </div>
            </div>
          </div>
        </div>
      </section>
    </>
  );
}
