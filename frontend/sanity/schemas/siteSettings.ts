import { defineField, defineType } from 'sanity';

export default defineType({
  name: 'siteSettings',
  title: 'Site Settings',
  type: 'document',
  fields: [
    defineField({
      name: 'companyName',
      title: 'Company Name',
      type: 'string',
      initialValue: 'Beacon Stone Realty',
    }),
    defineField({
      name: 'logo',
      title: 'Logo',
      type: 'image',
    }),
    defineField({
      name: 'address',
      title: 'Address',
      type: 'string',
    }),
    defineField({
      name: 'phone',
      title: 'Phone',
      type: 'string',
    }),
    defineField({
      name: 'email',
      title: 'Contact Email',
      type: 'string',
    }),
    defineField({
      name: 'heroVideoPlaybackId',
      title: 'Hero Video (Mux Playback ID)',
      type: 'string',
      description: 'Mux video playback ID for the homepage hero',
    }),
    defineField({
      name: 'socialLinks',
      title: 'Social Links',
      type: 'object',
      fields: [
        defineField({ name: 'instagram', title: 'Instagram URL', type: 'url' }),
        defineField({ name: 'linkedin', title: 'LinkedIn URL', type: 'url' }),
        defineField({ name: 'youtube', title: 'YouTube URL', type: 'url' }),
        defineField({ name: 'xiaohongshu', title: 'Xiaohongshu URL', type: 'url' }),
      ],
    }),
  ],
  preview: {
    select: { title: 'companyName' },
  },
});
