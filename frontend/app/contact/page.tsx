import ContactForm from '@/components/ContactForm';
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

const FALLBACK_HERO = 'https://uploads.kevv.ai/clientsuploads/beaconstone/luxury-house-pictures.jpg';

export default async function ContactPage() {
  let webInfo = DEFAULT_WEB_INFO;
  let heroImage = '';
  let introTitle = 'THANK YOU FOR CONTACTING BEACON STONE REALTY';
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

  const heroSrc = heroImage ? resolveAssetUrl(heroImage) : FALLBACK_HERO;

  const contactItems = [
    {
      label: 'Phone',
      value: webInfo.phone,
      href: webInfo.phone ? `tel:${webInfo.phone}` : undefined,
      icon: (
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
          <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.362 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.338 1.85.573 2.81.7A2 2 0 0 1 22 16.92z" />
        </svg>
      ),
    },
    {
      label: 'Email',
      value: webInfo.email,
      href: webInfo.email ? `mailto:${webInfo.email}` : undefined,
      icon: (
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
          <rect x="2" y="4" width="20" height="16" rx="2" />
          <path d="M22 4l-10 8L2 4" />
        </svg>
      ),
    },
    {
      label: 'Address',
      value: webInfo.address,
      href: undefined,
      icon: (
        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 1 1 18 0z" />
          <circle cx="12" cy="10" r="3" />
        </svg>
      ),
    },
  ].filter((item) => item.value);

  return (
    <>
      {/* Hero Banner */}
      <section className={styles.hero}>
        <img
          src={heroSrc}
          alt="Beacon Stone Realty"
          className={styles.heroImage}
        />
      </section>

      {/* Intro Section */}
      <section className={styles.intro}>
        <div className={styles.introInner}>
          <h1 className={styles.introTitle}>{introTitle.replace(/\u00a0/g, ' ')}</h1>
          {introContent ? (
            <div
              className={styles.introSubtitle}
              dangerouslySetInnerHTML={{ __html: introContent.replace(/&nbsp;/g, ' ').replace(/\u00a0/g, ' ') }}
            />
          ) : (
            <p className={styles.introSubtitle}>
              Thank you for reaching out. We look forward to assisting you with insight, precision, and a refined approach to New York real estate.
            </p>
          )}
        </div>
      </section>

      {/* Form Section */}
      <section className={styles.formSection}>
        <div className={styles.formWrapper}>
          <ContactForm />
        </div>
      </section>

      {/* Contact Cards */}
      {contactItems.length > 0 && (
        <section className={styles.cardsSection}>
          <div className={styles.cardsGrid}>
            {contactItems.map((item) => (
              <div key={item.label} className={styles.card}>
                <div className={styles.cardIcon}>{item.icon}</div>
                <p className={styles.cardLabel}>{item.label}</p>
                {item.href ? (
                  <a href={item.href} className={styles.cardValue}>
                    {item.value}
                  </a>
                ) : (
                  <p className={styles.cardValue}>{item.value}</p>
                )}
              </div>
            ))}
          </div>
        </section>
      )}
    </>
  );
}
