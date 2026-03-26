import { defineField, defineType } from 'sanity';

export default defineType({
  name: 'formSubmission',
  title: 'Form Submission',
  type: 'document',
  fields: [
    defineField({
      name: 'type',
      title: 'Submission Type',
      type: 'string',
      options: {
        list: [
          { title: 'Inquiry', value: 'inquiry' },
          { title: 'Join Application', value: 'join' },
          { title: 'Contact', value: 'contact' },
        ],
      },
    }),
    defineField({
      name: 'firstName',
      title: 'First Name',
      type: 'string',
    }),
    defineField({
      name: 'lastName',
      title: 'Last Name',
      type: 'string',
    }),
    defineField({
      name: 'email',
      title: 'Email',
      type: 'string',
    }),
    defineField({
      name: 'phone',
      title: 'Phone',
      type: 'string',
    }),
    defineField({
      name: 'message',
      title: 'Message',
      type: 'text',
    }),
    defineField({
      name: 'metadata',
      title: 'Additional Data',
      type: 'object',
      fields: [
        defineField({ name: 'budget', title: 'Budget', type: 'string' }),
        defineField({ name: 'bedrooms', title: 'Bedrooms', type: 'string' }),
        defineField({ name: 'timeline', title: 'Timeline', type: 'string' }),
        defineField({ name: 'location', title: 'Preferred Location', type: 'string' }),
        defineField({ name: 'market', title: 'Select Market', type: 'string' }),
        defineField({ name: 'linkedin', title: 'LinkedIn URL', type: 'string' }),
        defineField({ name: 'propertyId', title: 'Property ID', type: 'string' }),
        defineField({ name: 'agentId', title: 'Agent ID', type: 'string' }),
      ],
    }),
    defineField({
      name: 'status',
      title: 'Status',
      type: 'string',
      options: {
        list: [
          { title: 'New', value: 'new' },
          { title: 'Contacted', value: 'contacted' },
          { title: 'Closed', value: 'closed' },
        ],
      },
      initialValue: 'new',
    }),
  ],
  orderings: [
    {
      title: 'Newest First',
      name: 'createdAtDesc',
      by: [{ field: '_createdAt', direction: 'desc' }],
    },
  ],
  preview: {
    select: {
      firstName: 'firstName',
      lastName: 'lastName',
      type: 'type',
      status: 'status',
    },
    prepare({ firstName, lastName, type, status }) {
      return {
        title: `${firstName || ''} ${lastName || ''}`.trim(),
        subtitle: `${type || 'unknown'} · ${status || 'new'}`,
      };
    },
  },
});
