import { NextRequest, NextResponse } from 'next/server';
import { Resend } from 'resend';
import { sanityClient } from '@/sanity/client';

function getResend() {
  return new Resend(process.env.RESEND_API_KEY || 'placeholder');
}

const EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
const PHONE_PATTERN = /^[+\d\s().-]{7,20}$/;

interface FormPayload {
  type: 'inquiry' | 'join' | 'contact';
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  message?: string;
  metadata?: Record<string, string>;
}

function validate(body: FormPayload): string | null {
  if (!body.firstName?.trim()) return 'First name is required';
  if (!body.lastName?.trim()) return 'Last name is required';
  if (!body.email || !EMAIL_PATTERN.test(body.email)) return 'Valid email is required';
  if (!body.phone || !PHONE_PATTERN.test(body.phone)) return 'Valid phone number is required';
  if (!['inquiry', 'join', 'contact'].includes(body.type)) return 'Invalid form type';
  return null;
}

function buildOwnerEmail(data: FormPayload): string {
  const lines = [
    `New ${data.type} submission from beaconstonerealty.com`,
    '',
    `Name: ${data.firstName} ${data.lastName}`,
    `Email: ${data.email}`,
    `Phone: ${data.phone}`,
  ];

  if (data.message) lines.push(`Message: ${data.message}`);

  if (data.metadata) {
    for (const [key, value] of Object.entries(data.metadata)) {
      if (value) lines.push(`${key}: ${value}`);
    }
  }

  return lines.join('\n');
}

function buildConfirmationHtml(data: FormPayload): string {
  return `
    <div style="font-family: Georgia, serif; max-width: 600px; margin: 0 auto; color: #3e3634;">
      <h2 style="color: #3e3634;">Thank you, ${data.firstName}!</h2>
      <p>We have received your ${data.type === 'join' ? 'application' : 'inquiry'} and will be in touch shortly.</p>
      <hr style="border: none; border-top: 1px solid #d4c8c0; margin: 24px 0;" />
      <p style="font-size: 14px; color: #888;">
        Beacon Stone Realty<br />
        420 Lexington Ave #1454, New York, NY 10170<br />
        +1 646-696-8641
      </p>
    </div>
  `;
}

export async function POST(request: NextRequest) {
  try {
    const body = (await request.json()) as FormPayload;
    const error = validate(body);
    if (error) {
      return NextResponse.json({ success: false, error }, { status: 400 });
    }

    const resend = getResend();
    const fromEmail = process.env.RESEND_FROM_EMAIL || 'noreply@beaconstonerealty.com';
    const notifyEmail = process.env.RESEND_NOTIFY_EMAIL || 'info@beacon-stone.com';

    // 1. Store in Sanity (fire and forget — don't block response)
    const sanityPromise = sanityClient.create({
      _type: 'formSubmission',
      type: body.type,
      firstName: body.firstName,
      lastName: body.lastName,
      email: body.email,
      phone: body.phone,
      message: body.message || '',
      metadata: body.metadata || {},
      status: 'new',
    }).catch((err) => console.error('[Sanity] Failed to store submission:', err));

    // 2. Notify owner/agent
    const ownerEmailPromise = resend.emails.send({
      from: fromEmail,
      to: body.metadata?.agentEmail || notifyEmail,
      replyTo: body.email,
      subject: `[Beacon Stone Realty] New ${body.type} from ${body.firstName} ${body.lastName}`,
      text: buildOwnerEmail(body),
    }).catch((err) => console.error('[Resend] Failed to send owner email:', err));

    // 3. Send confirmation to user
    const userEmailPromise = resend.emails.send({
      from: fromEmail,
      to: body.email,
      subject: 'Thank you for contacting Beacon Stone Realty',
      html: buildConfirmationHtml(body),
    }).catch((err) => console.error('[Resend] Failed to send confirmation:', err));

    // Wait for all operations
    await Promise.allSettled([sanityPromise, ownerEmailPromise, userEmailPromise]);

    return NextResponse.json({ success: true });
  } catch {
    return NextResponse.json(
      { success: false, error: 'Internal server error' },
      { status: 500 },
    );
  }
}
