import Link from 'next/link';
import LegacyLeadForm from '@/components/LegacyLeadForm';
import styles from './page.module.css';
import { getGlobalData, getNewsDetail, getNewsList, type NewsItem } from '@/lib/api';

const FORM_NOTE_HTML = `
  <p>Sending this form opens your email app with a prepared message to Beacon Stone Realty. By continuing, you acknowledge our <a href="/page/61">Privacy Policy</a> and <a href="/page/61">Terms of Use</a>.</p>
`;

const FORM_DISCLAIMER_HTML = `
  <p>You can review and edit the draft before sending it from your own email account.</p>
`;

export const metadata = {
  title: 'Sale | Beaconstone Realty',
  description: 'Explore sale opportunities, partner with our advisors, and connect with Beacon Stone Realty.',
};

type SaleCard = Pick<NewsItem, 'id' | 'title' | 'url' | 'thumbnail' | 'keywords' | 'description' | 'content' | 'field'>;

function extractVideoSource(html: string): string | null {
  const videoMatch = html.match(/<video[^>]+src=["']([^"']+)["']/i);
  if (videoMatch?.[1]) {
    return videoMatch[1];
  }

  const sourceMatch = html.match(/<source[^>]+src=["']([^"']+)["']/i);
  return sourceMatch?.[1] ?? null;
}

export default async function SellWithUsPage() {
  let heroTitle = 'Sale';
  let heroDescription = '';
  let heroMedia = '';
  let advisorCards: SaleCard[] = [];
  let joinHeading = '';
  let joinDescription = '';
  let inquiryHeading = '';
  let inquiryDescription = '';
  let discoverMore: NewsItem[] = [];
  let recipientEmail = 'info@beacon-stone.com';

  try {
    const [globalData, hero, advisors, formContent, discoverContent] = await Promise.allSettled([
      getGlobalData(),
      getNewsDetail(50),
      getNewsList(6, -1, 9),
      Promise.all([getNewsDetail(52), getNewsDetail(53)]),
      getNewsList(9, -1, 8),
    ]);

    if (globalData.status === 'fulfilled') {
      recipientEmail = globalData.value.web_info.email || recipientEmail;
    }
    if (hero.status === 'fulfilled') {
      heroTitle = hero.value.title || heroTitle;
      heroDescription = hero.value.description || '';
      heroMedia = hero.value.content || '';
    }
    if (advisors.status === 'fulfilled') {
      advisorCards = advisors.value as SaleCard[];
    }
    if (formContent.status === 'fulfilled') {
      joinHeading = formContent.value[0].title;
      joinDescription = formContent.value[0].description;
      inquiryHeading = formContent.value[1].title;
      inquiryDescription = formContent.value[1].description;
    }
    if (discoverContent.status === 'fulfilled') {
      discoverMore = discoverContent.value;
    }
  } catch {
    // Keep the page shape intact if legacy content is temporarily unavailable.
  }

  const heroVideoSrc = extractVideoSource(heroMedia);

  return (
    <>
      <section className={styles.hero}>
        <div className="container">
          <div className={styles.heroGrid}>
            <div className={styles.heroCopy}>
              <p className={styles.heroEyebrow}>Sale</p>
              <h1 className={styles.heroTitle}>{heroTitle}</h1>
              {heroDescription && <p className={styles.heroDescription}>{heroDescription}</p>}
            </div>
            <div className={styles.heroMedia}>
              {heroVideoSrc ? (
                <video
                  autoPlay
                  muted
                  loop
                  playsInline
                  preload="metadata"
                  className={styles.heroVideo}
                >
                  <source src={heroVideoSrc} type="video/mp4" />
                </video>
              ) : heroMedia ? (
                <div dangerouslySetInnerHTML={{ __html: heroMedia }} />
              ) : (
                <div className={styles.heroFallback} />
              )}
            </div>
          </div>
        </div>
      </section>

      {advisorCards.length > 0 && (
        <section className={styles.advisors}>
          <div className="container">
            <div className={styles.sectionHeader}>
              <p className={styles.sectionEyebrow}>Expertise</p>
              <h2 className={styles.sectionTitle}>Work With Market Specialists</h2>
            </div>
            <div className={styles.advisorList}>
              {advisorCards.map((advisor) => (
                <article key={advisor.id} className={styles.advisorCard}>
                  <Link href={advisor.url || '#'} className={styles.advisorPhotoLink}>
                    {advisor.thumbnail && (
                      <img
                        src={advisor.thumbnail}
                        alt={advisor.title}
                        className={styles.advisorPhoto}
                        loading="lazy"
                      />
                    )}
                  </Link>
                  <div className={styles.advisorBody}>
                    <div className={styles.advisorMain}>
                      <Link href={advisor.url || '#'} className={styles.advisorName}>{advisor.title}</Link>
                      {advisor.keywords && <p className={styles.advisorRole}>{advisor.keywords}</p>}
                      <div className={styles.separator} />
                      {advisor.description && <p className={styles.advisorCompany}>{advisor.description}</p>}
                      {advisor.content && (
                        <div
                          className={styles.advisorContent}
                          dangerouslySetInnerHTML={{ __html: advisor.content }}
                        />
                      )}
                    </div>
                    <div className={styles.advisorContact}>
                      <span className={styles.contactTitle}>Contact</span>
                      {advisor.field?.phone && (
                        <a href={`tel:${advisor.field.phone}`} className={styles.contactLink}>O: {advisor.field.phone}</a>
                      )}
                      {advisor.field?.real_estate_broker_email && (
                        <a
                          href={`mailto:${advisor.field.real_estate_broker_email}`}
                          className={styles.contactLink}
                        >
                          {advisor.field.real_estate_broker_email}
                        </a>
                      )}
                      <Link href={advisor.url || '#'} className={styles.contactAction}>Send message</Link>
                    </div>
                  </div>
                </article>
              ))}
            </div>
          </div>
        </section>
      )}

      <section className={styles.formsSection}>
        <div className="container">
          <div className={styles.formsGrid}>
            <LegacyLeadForm
              variant="join"
              submissionTitle="Join as an agent"
              title={joinHeading || 'Join as an agent'}
              description={joinDescription || 'Introduce yourself, the market you focus on, and how you see your platform growing with Beacon Stone Realty.'}
              messagePlaceholder="Share your background and the kind of opportunities you want to build."
              noteHtml={FORM_NOTE_HTML}
              disclaimerHtml={FORM_DISCLAIMER_HTML}
              recipientEmail={recipientEmail}
              successMessage="Your email app has been opened with a recruiting inquiry draft."
            />
            <LegacyLeadForm
              variant="inquiry"
              submissionTitle={inquiryHeading || heroTitle}
              title={inquiryHeading || "Let's get in touch"}
              description={inquiryDescription || 'Tell us about your sale objectives and your email app will open with a prepared message.'}
              messagePlaceholder="I am interested in discussing a sale opportunity with Beacon Stone Realty."
              noteHtml={FORM_NOTE_HTML}
              disclaimerHtml={FORM_DISCLAIMER_HTML}
              recipientEmail={recipientEmail}
              successMessage="Your email app has been opened with a sale inquiry draft."
            />
          </div>
        </div>
      </section>

      {discoverMore.length > 0 && (
        <section className={styles.discoverSection}>
          <div className="container">
            <div className={styles.sectionHeader}>
              <p className={styles.sectionEyebrow}>Discover More</p>
              <h2 className={styles.sectionTitle}>Further Reading</h2>
            </div>
            <div className={styles.discoverGrid}>
              {discoverMore.map((item) => (
                <Link key={item.id} href={item.url || '#'} className={styles.discoverCard}>
                  {item.thumbnail && (
                    <img
                      src={item.thumbnail}
                      alt={item.title}
                      className={styles.discoverImage}
                      loading="lazy"
                    />
                  )}
                  <div className={styles.discoverBody}>
                    <h3>{item.title}</h3>
                    {item.keywords && <p className={styles.discoverEyebrow}>{item.keywords}</p>}
                  </div>
                </Link>
              ))}
            </div>
          </div>
        </section>
      )}
    </>
  );
}
