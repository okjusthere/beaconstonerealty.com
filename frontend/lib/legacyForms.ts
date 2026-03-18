export const LEGACY_EMAIL_PATTERN = /^[\w-]+(\.[\w-]+)*@[\w-]+(\.(\w)+)*(\.(\w){2,3})$/;
export const LEGACY_PHONE_PATTERN = /^(?:13|15|18|17)\d{9}$/;

export interface InquiryFormValues {
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  message: string;
  code: string;
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
  code: string;
}

export type LegacyLeadVariant = 'inquiry' | 'join' | 'contact';

export interface ValidationResult {
  error?: string;
  payload?: Record<string, string>;
}

function buildContacts(firstName: string, lastName: string): string {
  return `first Name:${firstName.trim()},last Name:${lastName.trim()}`;
}

function validateCommonFields(values: InquiryFormValues): string | undefined {
  if (!values.firstName.trim()) {
    return 'first Name！';
  }
  if (!values.lastName.trim()) {
    return 'last Name！';
  }
  if (!values.email.trim()) {
    return 'Email Address';
  }
  if (!LEGACY_EMAIL_PATTERN.test(values.email.trim())) {
    return 'Email address is invalid';
  }
  if (!values.phone.trim()) {
    return 'Phone number！';
  }
  if (!LEGACY_PHONE_PATTERN.test(values.phone.trim())) {
    return 'The format of the mobile phone number is incorrect！';
  }
  if (!values.code.trim()) {
    return 'Please enter the verification code.';
  }
  return undefined;
}

export function validateInquiryForm(values: InquiryFormValues, title: string): ValidationResult {
  const commonError = validateCommonFields(values);
  if (commonError) {
    return { error: commonError };
  }
  if (!values.message.trim()) {
    return { error: 'Please fill in the consultation content' };
  }

  return {
    payload: {
      title,
      contacts: buildContacts(values.firstName, values.lastName),
      phone: values.phone.trim(),
      email: values.email.trim(),
      message: values.message.trim(),
      code: values.code.trim(),
    },
  };
}

export function validateJoinForm(values: JoinFormValues, title: string): ValidationResult {
  const commonError = validateCommonFields(values);
  if (commonError) {
    return { error: commonError };
  }
  if (!values.market.trim()) {
    return { error: 'SelectMarket！' };
  }
  if (!values.linkedin.trim()) {
    return { error: 'LinkedIn！' };
  }
  if (!values.message.trim()) {
    return { error: 'Please fill in the consultation content' };
  }

  return {
    payload: {
      title,
      contacts: buildContacts(values.firstName, values.lastName),
      phone: values.phone.trim(),
      email: values.email.trim(),
      message: `SelectMarket:${values.market.trim()},LinkedIn:${values.linkedin.trim()},message:${values.message.trim()}`,
      code: values.code.trim(),
    },
  };
}

export function validateContactForm(values: ContactFormValues, title: string): ValidationResult {
  const commonError = validateCommonFields({
    firstName: values.firstName,
    lastName: values.lastName,
    email: values.email,
    phone: values.phone,
    message: 'contact us',
    code: values.code,
  });

  if (commonError) {
    return { error: commonError };
  }

  return {
    payload: {
      title,
      contacts: buildContacts(values.firstName, values.lastName),
      phone: values.phone.trim(),
      email: values.email.trim(),
      message: [
        `budget:${values.budget.trim()}`,
        `bedrooms:${values.bedrooms.trim()}`,
        `purchase:${values.purchase.trim()}`,
        `location:${values.location.trim()}`,
      ].join(','),
      code: values.code.trim(),
    },
  };
}

export async function sendLegacyVerificationCode(phone: string): Promise<{ ok: boolean; message: string }> {
  const trimmedPhone = phone.trim();

  if (!trimmedPhone) {
    return { ok: false, message: 'Phone number！' };
  }
  if (!LEGACY_PHONE_PATTERN.test(trimmedPhone)) {
    return { ok: false, message: 'The format of the mobile phone number is incorrect！' };
  }

  try {
    const response = await fetch('/api/legacy/send_sms', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ type: '1', mobile: trimmedPhone }).toString(),
    });
    const payload = await response.json();

    if (payload?.message === 'success') {
      return { ok: true, message: 'Verification code sent successfully.' };
    }

    return {
      ok: false,
      message: payload?.message || 'Failed to send the verification code.',
    };
  } catch {
    return { ok: false, message: 'Failed to send the verification code.' };
  }
}

export async function submitLegacyLead(payload: Record<string, string>): Promise<{ ok: boolean; message: string }> {
  try {
    const response = await fetch('/api/legacy/inner_message', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams(payload).toString(),
    });
    const data = await response.json();

    if (data?.message === 'success') {
      return { ok: true, message: 'success' };
    }

    return {
      ok: false,
      message: data?.message || 'Failed to submit the form.',
    };
  } catch {
    return { ok: false, message: 'Failed to submit the form.' };
  }
}
