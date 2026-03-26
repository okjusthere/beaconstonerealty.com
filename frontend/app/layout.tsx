import type { Metadata } from 'next';
import './globals.css';
import Header from '@/components/Header';
import Footer from '@/components/Footer';
import { getGlobalData, getPicByClassId } from '@/lib/api';

export const metadata: Metadata = {
  title: 'Beaconstone Realty | Luxury Real Estate',
  description: 'Beaconstone Realty — premium luxury real estate services. Find exclusive properties and connect with top real estate professionals.',
  keywords: 'luxury real estate, Beaconstone Realty, premium properties, real estate broker',
};

export default async function RootLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  let globalData;
  try {
    globalData = await getGlobalData();
  } catch {
    // Fallback: render without global data during build / when API is unavailable
    globalData = null;
  }

  const menuItems = globalData?.menu_info ?? [];
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
        <link rel="icon" href={logo?.path || '/favicon.ico'} />
        <link rel="shortcut icon" href={logo?.path || '/favicon.ico'} />
        <link rel="apple-touch-icon" href={logo?.path || '/favicon.ico'} />
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
        <Footer webInfo={webInfo} />
      </body>
    </html>
  );
}
