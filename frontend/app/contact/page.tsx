import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { findMenuByPath, getGlobalData, getNewsDetail, resolveAssetUrl, type WebInfo } from '@/lib/api';

export const metadata = {
  title: 'Contact Us | Beaconstone Realty',
  description: 'Get in touch with Beaconstone Realty for buying, selling, and advisory services.',
};

const DEFAULT_WEB_INFO: WebInfo = {
  company: 'Beacon Stone Realty',
  address: '',
  phone: '',
  mobile: '',
  email: '',
  fax: '',
  contact: '',
  qq: '',
  wechat: '',
  whatsapp: '',
  zip: '',
  icp: '',
  icp_police: '',
  weburl: '',
  map: '',
};

export default async function ContactPage() {
  let webInfo = DEFAULT_WEB_INFO;
  let heroImage = '';
  let introTitle = 'Contact Us';
  let introContent = '';

  try {
    const [globalData, introData] = await Promise.allSettled([
      getGlobalData(),
      getNewsDetail(58),
    ]);

    if (globalData.status === 'fulfilled') {
      webInfo = globalData.value.web_info;
      const menu = findMenuByPath(globalData.value.menu_info, '/contact');
      heroImage = menu?.banner?.[0] || menu?.thumbnail || '';
    }

    if (introData.status === 'fulfilled') {
      introTitle = introData.value.title || introTitle;
      introContent = introData.value.content || '';
    }
  } catch {
    // Keep the legacy fallback copy visible if the API is unavailable.
  }

  const contactItems = [
    { label: 'Phone', value: webInfo.phone, href: webInfo.phone ? `tel:${webInfo.phone}` : undefined },
    { label: 'Email', value: webInfo.email, href: webInfo.email ? `mailto:${webInfo.email}` : undefined },
    { label: 'Address', value: webInfo.address, href: undefined },
  ].filter((item) => item.value);

  return (
    <>
      <section className={styles.hero}>
        {heroImage ? (
          <img
            src={resolveAssetUrl(heroImage)}
            alt={introTitle}
            className={styles.heroImage}
          />
        ) : (
          <div className={styles.heroFallback} />
        )}
      </section>

      <section className={styles.intro}>
        <div className="container">
          <div className={styles.introInner}>
            <h1 className={styles.introTitle}>{introTitle}</h1>
            {introContent && (
              <div
                className={styles.introContent}
                dangerouslySetInnerHTML={{ __html: introContent }}
              />
            )}
          </div>
        </div>
      </section>

      <section className={styles.content}>
        <div className="container">
          <div className={styles.grid}>
            <LegacyLeadForm
              variant="contact"
              submissionTitle="contact us"
              eyebrow="Contact Us"
              title="Tell us what you are looking for"
              description="Share your timeline, preferred areas, and bedroom needs. We will reach out once your request has been reviewed."
              successMessage="Thank you. Your contact request has been submitted."
            />

            <aside className={styles.sidebar}>
              <div className={styles.sidebarCard}>
                <p className={styles.sidebarEyebrow}>Beacon Stone Realty</p>
                <h2 className={styles.sidebarTitle}>Get In Touch</h2>
                <p className={styles.sidebarText}>
                  Our team handles private residences, investment opportunities, and cross-border acquisitions across New York City.
                </p>
                <ul className={styles.contactList}>
                  {contactItems.map((item) => (
                    <li key={item.label} className={styles.contactItem}>
                      <span className={styles.contactLabel}>{item.label}</span>
                      {item.href ? (
                        <a href={item.href} className={styles.contactValue}>{item.value}</a>
                      ) : (
                        <p className={styles.contactValue}>{item.value}</p>
                      )}
                    </li>
                  ))}
                </ul>
              </div>
            </aside>
          </div>
        </div>
      </section>
    </>
  );
}
