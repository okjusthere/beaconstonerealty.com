import { expect, test, type Page } from '@playwright/test';

function trackClientErrors(page: Page) {
  const errors: string[] = [];

  page.on('console', (message) => {
    if (message.type() === 'error') {
      errors.push(message.text());
    }
  });

  page.on('pageerror', (error) => {
    errors.push(error.message);
  });

  return errors;
}

async function collectSameOriginAssets(page: Page) {
  return page.evaluate(() => {
    const origin = window.location.origin;
    const urls = new Set<string>();

    const addUrl = (value: string | null | undefined) => {
      if (!value) {
        return;
      }

      const absoluteUrl = new URL(value, origin).href;
      if (absoluteUrl.startsWith(origin)) {
        urls.add(absoluteUrl);
      }
    };

    Array.from(document.images).forEach((image) => addUrl(image.currentSrc || image.getAttribute('src')));
    Array.from(document.querySelectorAll('video')).forEach((video) => addUrl((video as HTMLVideoElement).currentSrc || video.getAttribute('src')));
    Array.from(document.querySelectorAll('source')).forEach((source) => addUrl(source.getAttribute('src')));

    return Array.from(urls);
  });
}

test('homepage keeps the title block above the Mux hero embed and centers the feature sections', async ({ page, request }) => {
  const errors = trackClientErrors(page);

  await page.goto('/', { waitUntil: 'domcontentloaded' });

  const heroHeading = page.getByRole('heading', {
    level: 1,
    name: /Every home tells a story\. Let yours begin here/i,
  });
  const heroFrame = page.locator('main mux-player[playback-id="02PfbniOLPqerXd2XUwjIyrrl01F01asVS802OqdUvS6a01Q"]').first();

  await expect(heroHeading).toBeVisible();
  await expect(heroFrame).toBeVisible();
  await expect(page.locator('h1 br')).toHaveCount(1);
  await expect(heroFrame).toHaveAttribute('playback-id', '02PfbniOLPqerXd2XUwjIyrrl01F01asVS802OqdUvS6a01Q');
  await expect.poll(async () => heroFrame.evaluate((node) => Boolean((node as HTMLMediaElement & { muted?: boolean }).muted))).toBe(true);

  const headingBox = await heroHeading.boundingBox();
  const videoBox = await heroFrame.boundingBox();

  expect(headingBox).not.toBeNull();
  expect(videoBox).not.toBeNull();
  expect((headingBox?.y ?? 0) + (headingBox?.height ?? 0)).toBeLessThan((videoBox?.y ?? 0) + 8);

  const aboutHeading = page.getByRole('heading', { level: 2, name: /Beacon Stone Realty/i });
  const aboutImage = page.locator('img[alt="Beacon Stone Realty"]').first();
  const storyHeading = page.getByRole('heading', { level: 2, name: /Our Story/i });
  const storyImage = page.locator('img[alt="Our Story"]').first();

  await expect(aboutHeading).toBeVisible();
  await expect(aboutImage).toBeVisible();
  await expect(storyHeading).toBeVisible();
  await expect(storyImage).toBeVisible();

  const aboutHeadingBox = await aboutHeading.boundingBox();
  const aboutImageBox = await aboutImage.boundingBox();
  const storyHeadingBox = await storyHeading.boundingBox();
  const storyImageBox = await storyImage.boundingBox();

  expect(aboutHeadingBox).not.toBeNull();
  expect(aboutImageBox).not.toBeNull();
  expect(storyHeadingBox).not.toBeNull();
  expect(storyImageBox).not.toBeNull();
  expect((aboutHeadingBox?.x ?? 0)).toBeGreaterThan((aboutImageBox?.x ?? 0) + ((aboutImageBox?.width ?? 0) * 0.6));
  expect((storyImageBox?.x ?? 0)).toBeGreaterThan((storyHeadingBox?.x ?? 0) + ((storyHeadingBox?.width ?? 0) * 0.8));
  expect(Math.abs((storyHeadingBox?.y ?? 0) - (storyImageBox?.y ?? 0))).toBeLessThan(140);

  const exclusiveHeading = page.getByRole('heading', { level: 2, name: /Representation, Defined by Elegance/i });
  const exclusiveButton = page.getByRole('link', { name: /Sell With Us/i }).last();

  await expect(exclusiveHeading).toBeVisible();
  await expect(exclusiveButton).toBeVisible();

  const exclusiveHeadingBox = await exclusiveHeading.boundingBox();
  const exclusiveButtonBox = await exclusiveButton.boundingBox();
  const viewportCenterX = page.viewportSize()?.width ? page.viewportSize()!.width / 2 : 0;

  expect(exclusiveHeadingBox).not.toBeNull();
  expect(exclusiveButtonBox).not.toBeNull();
  expect(Math.abs(((exclusiveHeadingBox?.x ?? 0) + ((exclusiveHeadingBox?.width ?? 0) / 2)) - viewportCenterX)).toBeLessThan(120);
  expect(Math.abs(((exclusiveButtonBox?.x ?? 0) + ((exclusiveButtonBox?.width ?? 0) / 2)) - viewportCenterX)).toBeLessThan(120);

  const assetUrls = await collectSameOriginAssets(page);
  expect(assetUrls.length).toBeGreaterThan(0);

  for (const assetUrl of assetUrls) {
    const response = await request.get(assetUrl);
    expect(response.ok(), `${assetUrl} should resolve successfully`).toBeTruthy();
  }

  expect(errors).toEqual([]);
});

test('header uses the same brown background on home and interior pages', async ({ page }) => {
  await page.goto('/', { waitUntil: 'domcontentloaded' });
  const homeHeaderColor = await page.locator('header').evaluate((node) => window.getComputedStyle(node).backgroundColor);

  await page.goto('/about/13/', { waitUntil: 'domcontentloaded' });
  const aboutHeaderColor = await page.locator('header').evaluate((node) => window.getComputedStyle(node).backgroundColor);

  expect(homeHeaderColor).toBe('rgb(62, 54, 52)');
  expect(aboutHeaderColor).toBe('rgb(62, 54, 52)');
});

test('critical static routes and legacy aliases respond successfully', async ({ request }) => {
  const routes = [
    '/',
    '/about/13/',
    '/properties/',
    '/properties/30/',
    '/brokers/',
    '/brokers/74/',
    '/contact/',
    '/contact/57/',
    '/sell-with-us/',
    '/sale/38/',
    '/joinUs/39/',
    '/newsdetail/17/',
    '/page/61/',
    '/propertyCenter/5/',
    '/propertyCenterDetail/30/',
    '/realEstateBrokerCenter/6/',
    '/realEstateBrokerDetail/74/',
  ];

  for (const route of routes) {
    const response = await request.get(route);
    expect(response.status(), `${route} should return HTTP 200`).toBe(200);
  }

  const notFoundResponse = await request.get('/this-route-does-not-exist/');
  expect(notFoundResponse.status()).toBe(404);
});

test('legacy alias pages resolve to the expected content', async ({ page }) => {
  await page.goto('/propertyCenterDetail/30/', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: /Prime Residences/i })).toBeVisible();

  await page.goto('/realEstateBrokerDetail/74/', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: /Xiangyu \(Allen\) Zhang/i })).toBeVisible();

  await page.goto('/page/61/', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: /Terms of Use/i })).toBeVisible();
});

test('sell-with-us page renders advisor content and keeps only the sale inquiry form', async ({ page }) => {
  await page.goto('/sell-with-us/', { waitUntil: 'domcontentloaded' });

  await expect(page.getByRole('heading', { level: 1, name: /Sell with Us/i })).toBeVisible();
  const saleFrame = page.locator('main mux-player[playback-id="02PfbniOLPqerXd2XUwjIyrrl01F01asVS802OqdUvS6a01Q"]').first();
  await expect(saleFrame).toBeVisible();
  await expect(saleFrame).toHaveAttribute('playback-id', '02PfbniOLPqerXd2XUwjIyrrl01F01asVS802OqdUvS6a01Q');
  await expect.poll(async () => saleFrame.evaluate((node) => Boolean((node as HTMLMediaElement & { muted?: boolean }).muted))).toBe(false);
  await expect(page.getByRole('heading', { level: 2, name: /Work With Market Specialists/i })).toBeVisible();
  await expect(page.getByRole('link', { name: /Xiangyu \(Allen\) Zhang/i }).first()).toBeVisible();
  await expect(page.locator('form')).toHaveCount(1);
  await expect(page.getByLabel('Select Market')).toHaveCount(0);

  const inquiryForm = page.locator('form').first();
  await inquiryForm.getByLabel('First Name').fill('Alan');
  await inquiryForm.getByLabel('Last Name').fill('Turing');
  await inquiryForm.getByLabel('Email Address').fill('alan@example.com');
  await inquiryForm.getByLabel('Phone Number').fill('212-555-0110');
  await inquiryForm.getByRole('button', { name: /Send Message/i }).click();
  await expect(inquiryForm.getByText('Please fill in the message.')).toBeVisible();
});

test.describe('contact form validation', () => {
  test.use({ viewport: { width: 1440, height: 1200 } });

  test('shows validation feedback before trying to open mail', async ({ page }) => {
    await page.goto('/contact/', { waitUntil: 'domcontentloaded' });

    await page.getByRole('button', { name: /Send Message/i }).click();
    await expect(page.getByText('First name is required.')).toBeVisible();

    await page.getByLabel('First Name').fill('Ada');
    await page.getByLabel('Last Name').fill('Lovelace');
    await page.getByLabel('Email Address').fill('not-an-email');
    await page.getByLabel('Phone Number').fill('123');
    await page.getByRole('button', { name: /Send Message/i }).click();

    await expect(page.getByText('Email address is invalid.')).toBeVisible();
  });
});

test('property and broker detail pages expose the expected static content', async ({ page }) => {
  await page.goto('/properties/30/', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: /Prime Residences/i })).toBeVisible();
  await expect(page.getByRole('heading', { level: 2, name: /Development Details/i })).toBeVisible();
  await expect(page.getByRole('link', { name: /Agent Profile/i })).toHaveAttribute('href', '/brokers/73/');

  const propertyForm = page.locator('form').first();
  await propertyForm.getByLabel('First Name').fill('Grace');
  await propertyForm.getByLabel('Last Name').fill('Hopper');
  await propertyForm.getByLabel('Email Address').fill('grace@example.com');
  await propertyForm.getByLabel('Phone Number').fill('212-555-0199');
  await propertyForm.getByRole('button', { name: /Send Message/i }).click();
  await expect(propertyForm.getByText('Please fill in the message.')).toBeVisible();

  await page.goto('/brokers/74/', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: /Xiangyu \(Allen\) Zhang/i })).toBeVisible();
  await expect(page.getByRole('link', { name: /allenzhang@beacon-stone\.com/i })).toBeVisible();

  const brokerForm = page.locator('form').first();
  await brokerForm.getByLabel('First Name').fill('Katherine');
  await brokerForm.getByLabel('Last Name').fill('Johnson');
  await brokerForm.getByLabel('Email Address').fill('katherine@example.com');
  await brokerForm.getByLabel('Phone Number').fill('212-555-0133');
  await brokerForm.getByRole('button', { name: /Send Message/i }).click();
  await expect(brokerForm.getByText('Please fill in the message.')).toBeVisible();
});

test('about and broker index pages restore the missing legacy sections and long-form bios', async ({ page }) => {
  await page.goto('/about/13/', { waitUntil: 'domcontentloaded' });
  await expect(page.locator('main > section').first().locator('video')).toHaveCount(0);
  await expect(page.getByText(/An International Network/i)).toBeVisible();
  const advisorsHeading = page.getByRole('heading', { level: 2, name: /Advisors, Not Just Agents/i });
  await expect(advisorsHeading).toBeVisible();
  await expect(page.getByText(/global network of exceptional agents and exclusive properties/i)).toBeVisible();
  await expect(page.getByRole('link', { name: /Xiangyu \(Allen\) Zhang/i }).first()).toHaveAttribute('href', '/brokers/74/');
  await expect(page.getByText(/View Profile/i).first()).toBeVisible();

  const aboutHeroColor = await page.locator('main > section').first().evaluate((node) => window.getComputedStyle(node).backgroundColor);
  const aboutAdvisorsColor = await advisorsHeading.evaluate((node) => window.getComputedStyle(node.closest('section') as Element).backgroundColor);

  expect(aboutHeroColor).toBe('rgb(62, 54, 52)');
  expect(aboutAdvisorsColor).toBe('rgb(62, 54, 52)');

  await page.goto('/brokers/', { waitUntil: 'domcontentloaded' });
  await expect(page.getByRole('heading', { level: 1, name: /Guided by Expertise\. Driven by Strategy/i })).toBeVisible();
  await expect(page.getByText(/New York.?based real estate investor and Founder/i)).toBeVisible();
  await expect(page.getByRole('link', { name: /allenzhang@beacon-stone\.com/i })).toBeVisible();
  await expect(page.getByRole('link', { name: /qiaochen@beacon-stone\.com/i })).toBeVisible();
});

test('join-us page renders media, feature sections, and discover-more links', async ({ page }) => {
  await page.goto('/joinUs/39/', { waitUntil: 'domcontentloaded' });

  const joinHeroHeading = page.getByRole('heading', { level: 1, name: /Join Us/i });
  await expect(joinHeroHeading).toBeVisible();
  await expect(page.locator('main > section').first().locator('video')).toHaveCount(0);
  await expect(page.getByRole('heading', { level: 2, name: /A more thoughtful real estate experience\./i })).toBeVisible();
  const careersHeading = page.getByRole('heading', { level: 2, name: /Why Beacon Stone Realty/i });
  const discoverHeading = page.getByRole('heading', { level: 2, name: /Discover More/i });
  await expect(careersHeading).toBeVisible();
  await expect(discoverHeading).toBeVisible();
  await expect(page.getByRole('heading', { level: 2, name: /Give yourself every advantage/i })).toBeVisible();

  const discoverLinks = page.locator('a').filter({ hasText: /About Us|Sell with Us|Real Estate Agent Center/i });
  await expect(discoverLinks.first()).toBeVisible();

  const joinHeroColor = await joinHeroHeading.evaluate((node) => window.getComputedStyle(node.closest('section') as Element).backgroundColor);
  const joinCareersColor = await page.locator('main > section').nth(3).evaluate((node) => window.getComputedStyle(node).backgroundColor);
  const joinDiscoverColor = await page.locator('main > section').nth(4).evaluate((node) => window.getComputedStyle(node).backgroundColor);

  expect(joinHeroColor).toBe('rgb(62, 54, 52)');
  expect(joinCareersColor).toBe('rgb(62, 54, 52)');
  expect(joinDiscoverColor).toBe('rgb(62, 54, 52)');

  const joinForm = page.locator('form').first();
  await joinForm.getByLabel('First Name').fill('Ada');
  await joinForm.getByLabel('Last Name').fill('Lovelace');
  await joinForm.getByLabel('Email Address').fill('ada@example.com');
  await joinForm.getByLabel('Phone Number').fill('212-555-0182');
  await joinForm.getByRole('button', { name: /Send Message/i }).click();
  await expect(joinForm.getByText('Select market is required.')).toBeVisible();
});

test.describe('mobile homepage layout', () => {
  test.use({ viewport: { width: 390, height: 844 } });

  test('keeps the hero copy and media separated on small screens', async ({ page }) => {
    await page.goto('/', { waitUntil: 'domcontentloaded' });

    const heroHeading = page.getByRole('heading', {
      level: 1,
      name: /Every home tells a story\. Let yours begin here/i,
    });
    const heroFrame = page.locator('main mux-player[playback-id="02PfbniOLPqerXd2XUwjIyrrl01F01asVS802OqdUvS6a01Q"]').first();

    await expect(heroHeading).toBeVisible();
    await expect(heroFrame).toBeVisible();

    const headingBox = await heroHeading.boundingBox();
    const videoBox = await heroFrame.boundingBox();

    expect(headingBox).not.toBeNull();
    expect(videoBox).not.toBeNull();
    expect((headingBox?.y ?? 0) + (headingBox?.height ?? 0)).toBeLessThan((videoBox?.y ?? 0) + 8);
  });
});
