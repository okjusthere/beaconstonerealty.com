import { NextRequest, NextResponse } from 'next/server';
import { proxyLegacyRequestRaw } from '@/lib/api';

export const dynamic = 'force-dynamic';
const LEGACY_SESSION_COOKIE = 'legacy_phpsessid';

type RouteContext = {
  params: Promise<{
    endpoint: string;
  }>;
};

function getSearchParams(request: NextRequest): Record<string, string> {
  return Object.fromEntries(request.nextUrl.searchParams.entries());
}

async function getBodyParams(request: NextRequest): Promise<Record<string, string>> {
  const contentType = request.headers.get('content-type') || '';

  if (contentType.includes('application/json')) {
    const payload = await request.json();
    return Object.fromEntries(
      Object.entries(payload as Record<string, unknown>).map(([key, value]) => [key, String(value ?? '')]),
    );
  }

  if (contentType.includes('application/x-www-form-urlencoded')) {
    const text = await request.text();
    return Object.fromEntries(new URLSearchParams(text).entries());
  }

  const formData = await request.formData();
  return Object.fromEntries(
    Array.from(formData.entries()).map(([key, value]) => [key, String(value ?? '')]),
  );
}

function getLegacySessionCookie(request: NextRequest): string | undefined {
  const value = request.cookies.get(LEGACY_SESSION_COOKIE)?.value;
  return value ? `PHPSESSID=${value}` : undefined;
}

function extractPhpSessionId(setCookie: string | null): string | undefined {
  if (!setCookie) {
    return undefined;
  }

  const match = setCookie.match(/PHPSESSID=([^;]+)/i);
  return match?.[1];
}

async function handleProxyRequest(
  endpoint: string,
  params: Record<string, string>,
  options: { method?: 'GET' | 'POST' },
  request: NextRequest,
) {
  const { payload, setCookie } = await proxyLegacyRequestRaw(
    `${endpoint}.php`,
    params,
    {
      ...options,
      cookieHeader: getLegacySessionCookie(request),
    },
  );

  const response = NextResponse.json(payload);
  const sessionId = extractPhpSessionId(setCookie);
  if (sessionId) {
    response.cookies.set({
      name: LEGACY_SESSION_COOKIE,
      value: sessionId,
      httpOnly: true,
      sameSite: 'lax',
      path: '/',
    });
  }

  return response;
}

export async function GET(request: NextRequest, context: RouteContext) {
  const { endpoint } = await context.params;
  return handleProxyRequest(endpoint, getSearchParams(request), {}, request);
}

export async function POST(request: NextRequest, context: RouteContext) {
  const { endpoint } = await context.params;
  return handleProxyRequest(endpoint, await getBodyParams(request), { method: 'POST' }, request);
}
