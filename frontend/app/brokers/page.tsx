import styles from './page.module.css';
import Link from 'next/link';
import { getGlobalData, getNewsList, findMenuById } from '@/lib/api';

export const metadata = {
  title: 'Our Agents | Beaconstone Realty',
  description: 'Meet our team of luxury real estate professionals at Beaconstone Realty.',
};

export default async function BrokersPage() {
  let pageTitle = 'Our Real Estate Professionals';
  let bannerImage = '';
  let agents: Array<{
    id: number; title: string; url: string; thumbnail: string; description: string;
  }> = [];

  try {
    const [globalData, agentList] = await Promise.allSettled([
      getGlobalData(),
      getNewsList(10, -1, 2),
    ]);

    if (globalData.status === 'fulfilled') {
      // Get broker center menu info  
      const brokerMenu = findMenuById(globalData.value.menu_info, 10);
      if (brokerMenu) {
        pageTitle = brokerMenu.remarks || brokerMenu.title || pageTitle;
        bannerImage = brokerMenu.thumbnail || '';
      }
    }
    if (agentList.status === 'fulfilled') {
      agents = agentList.value as typeof agents;
    }
  } catch {}

  return (
    <>
      {/* Hero Banner */}
      <section className={styles.hero}>
        {bannerImage && (
          <img src={bannerImage} alt={pageTitle} className={styles.heroBg} loading="eager" />
        )}
        <div className={styles.heroOverlay} />
        <div className={styles.heroContent}>
          <h1 className={styles.heroTitle}>{pageTitle}</h1>
        </div>
      </section>

      {/* Agents Grid */}
      <section className={`section-lg ${styles.agents}`}>
        <div className="container">
          <div className={styles.agentsGrid}>
            {agents.map((agent) => (
              <Link key={agent.id} href={agent.url || '#'} className={styles.agentCard}>
                <div className={styles.agentImageWrap}>
                  {agent.thumbnail && (
                    <img src={agent.thumbnail} alt={agent.title} loading="lazy" className={styles.agentImage} />
                  )}
                </div>
                <div className={styles.agentInfo}>
                  <h3 className={styles.agentName}>{agent.title}</h3>
                  <p className={styles.agentRole}>Real Estate Professional</p>
                  <p className={styles.agentDesc}>{agent.description}</p>
                </div>
              </Link>
            ))}
          </div>

          {agents.length === 0 && (
            <div className={styles.empty}>
              <p>No agents found. Please check back later.</p>
            </div>
          )}
        </div>
      </section>
    </>
  );
}
