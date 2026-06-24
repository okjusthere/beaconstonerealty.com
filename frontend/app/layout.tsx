import type { Metadata } from 'next';
import './globals.css';
import Header from '@/components/Header';
import Footer, { type FooterSocialLinks } from '@/components/Footer';
import { getGlobalData, getPicByClassId, type MenuItem } from '@/lib/api';
import { getSiteSettings } from '@/sanity/fetch';

export const metadata: Metadata = {
  title: 'Beaconstone Realty | Luxury Real Estate',
  description: 'Beaconstone Realty — premium luxury real estate services. Find exclusive properties and connect with top real estate professionals.',
  keywords: 'luxury real estate, Beaconstone Realty, premium properties, real estate broker',

};

const NEWS_MENU_ITEM: MenuItem = {
  id: 10001,
  parentid: 0,
  type: 2,
  link_id: 0,
  title: 'Real Estate News',
  sub_title: 'Real Estate News',
  url: '/news',
  remarks: '',
  thumbnail: '',
  banner: [],
  is_show: true,
  children: [],
};

function withNewsMenuItem(menuItems: MenuItem[]): MenuItem[] {
  if (menuItems.some((item) => item.url === '/news' || item.title.toLowerCase() === 'real estate news')) {
    return menuItems;
  }

  const homeIndex = menuItems.findIndex(
    (item) => item.title.toLowerCase() === 'home' || item.url === '/' || item.url === '/index',
  );

  if (homeIndex === -1) {
    return [NEWS_MENU_ITEM, ...menuItems];
  }

  return [
    ...menuItems.slice(0, homeIndex + 1),
    NEWS_MENU_ITEM,
    ...menuItems.slice(homeIndex + 1),
  ];
}

export default async function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  let globalData;
  let siteSettings: { socialLinks?: FooterSocialLinks | null } | null = null;
  try {
    [globalData, siteSettings] = await Promise.all([
      getGlobalData(),
      getSiteSettings() as Promise<{ socialLinks?: FooterSocialLinks | null } | null>,
    ]);
  } catch {
    // Fallback: render without global data during build / when API is unavailable
    globalData = null;
    siteSettings = null;
  }

  const menuItems = withNewsMenuItem(globalData?.menu_info ?? []);
  const webInfo = globalData?.web_info ?? {
    company: 'Beaconstone Realty',
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
  const logo = globalData ? getPicByClassId(globalData.pic_info, 1) : undefined;

  return (
    <html lang="en">
      <head>
        <link rel="preconnect" href="https://fonts.googleapis.com" />
        <link rel="preconnect" href="https://fonts.gstatic.com" crossOrigin="" />
        <link
          href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Libre+Baskerville:ital,wght@0,400;0,700;1,400&display=swap"
          rel="stylesheet"
        />
      </head>
      <body>
        <Header menuItems={menuItems} logo={logo} />
        <main>
          {children}
        </main>
        <Footer webInfo={webInfo} socialLinks={siteSettings?.socialLinks} />
      </body>
    </html>
  );
}
