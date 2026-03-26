export interface FormSubmitResult {
  success: boolean;
  error?: string;
}

export interface BaseFormData {
  type: 'inquiry' | 'join' | 'contact';
  firstName: string;
  lastName: string;
  email: string;
  phone: string;
  message?: string;
  metadata?: Record<string, string>;
}

export async function submitForm(data: BaseFormData): Promise<FormSubmitResult> {
  try {
    const res = await fetch('/api/forms', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(data),
    });

    const json = await res.json();

    if (!res.ok) {
      return { success: false, error: json.error || 'Submission failed' };
    }

    return { success: true };
  } catch {
    return { success: false, error: 'Network error. Please try again.' };
  }
}
