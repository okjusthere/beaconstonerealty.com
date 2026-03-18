export const LEGACY_EMAIL_PATTERN = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
export const LEGACY_PHONE_PATTERN = /^[+\d\s().-]{7,20}$/;

export interface InquiryFormValues {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  message: string;
}

export interface JoinFormValues extends InquiryFormValues {
  market: string;
  linkedin: string;
}

export interface ContactFormValues {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  budget: string;
  bedrooms: string;
  purchase: string;
  location: string;
}

export type LegacyLeadVariant = 'inquiry' | 'join' | 'contact';

export interface LeadDraft {
  subject: string;
  body: string;
}

export interface ValidationResult {
  error?: string;
  payload?: LeadDraft;
}

function normalizePhone(value: string): string {
  return value.replace(/\D/g, '');
}

function buildContacts(firstName: string, lastName: string): string {
  return `${firstName.trim()} ${lastName.trim()}`.trim();
}

function buildDraft(title: string, lines: Array<[string, string]>): LeadDraft {
  return {
    subject: `[Beacon Stone Realty] ${title}`,
    body: lines
      .filter(([, value]) => value.trim())
      .map(([label, value]) => `${label}: ${value.trim()}`)
      .join('\n'),
  };
}

function validateCommonFields(values: InquiryFormValues): string | undefined {
  if (!values.firstName.trim()) {
    return 'First name is required.';
  }
  if (!values.lastName.trim()) {
    return 'Last name is required.';
  }
  if (!values.email.trim()) {
    return 'Email address is required.';
  }
  if (!LEGACY_EMAIL_PATTERN.test(values.email.trim())) {
    return 'Email address is invalid.';
  }
  if (!values.phone.trim()) {
    return 'Phone number is required.';
  }
  if (!LEGACY_PHONE_PATTERN.test(values.phone.trim()) || normalizePhone(values.phone).length < 7) {
    return 'Phone number format is invalid.';
  }
  return undefined;
}

export function validateInquiryForm(values: InquiryFormValues, title: string): ValidationResult {
  const commonError = validateCommonFields(values);
  if (commonError) {
    return { error: commonError };
  }
  if (!values.message.trim()) {
    return { error: 'Please fill in the message.' };
  }

  return {
    payload: buildDraft(title, [
      ['Inquiry type', 'Property / advisor inquiry'],
      ['Full name', buildContacts(values.firstName, values.lastName)],
      ['Email', values.email],
      ['Phone', values.phone],
      ['Message', values.message],
    ]),
  };
}

export function validateJoinForm(values: JoinFormValues, title: string): ValidationResult {
  const commonError = validateCommonFields(values);
  if (commonError) {
    return { error: commonError };
  }
  if (!values.market.trim()) {
    return { error: 'Select market is required.' };
  }
  if (!values.linkedin.trim()) {
    return { error: 'LinkedIn URL is required.' };
  }
  if (!values.message.trim()) {
    return { error: 'Please fill in the message.' };
  }

  return {
    payload: buildDraft(title, [
      ['Inquiry type', 'Join us'],
      ['Full name', buildContacts(values.firstName, values.lastName)],
      ['Email', values.email],
      ['Phone', values.phone],
      ['Select market', values.market],
      ['LinkedIn', values.linkedin],
      ['Message', values.message],
    ]),
  };
}

export function validateContactForm(values: ContactFormValues, title: string): ValidationResult {
  const commonError = validateCommonFields({
    firstName: values.firstName,
    lastName: values.lastName,
    email: values.email,
    phone: values.phone,
    message: 'contact request',
  });

  if (commonError) {
    return { error: commonError };
  }

  return {
    payload: buildDraft(title, [
      ['Inquiry type', 'Contact request'],
      ['Full name', buildContacts(values.firstName, values.lastName)],
      ['Email', values.email],
      ['Phone', values.phone],
      ['Budget', values.budget],
      ['Bedrooms', values.bedrooms],
      ['Purchase timeline', values.purchase],
      ['Preferred location', values.location],
    ]),
  };
}

export function buildMailtoHref(recipientEmail: string, payload: LeadDraft): string {
  const query = new URLSearchParams({
    subject: payload.subject,
    body: payload.body,
  });

  return `mailto:${recipientEmail}?${query.toString()}`;
}
