import { defineField, defineType } from 'sanity';

export default defineType({
  name: 'listing',
  title: 'Property Listing',
  type: 'document',
  fields: [
    defineField({
      name: 'title',
      title: 'Property Title',
      type: 'string',
      validation: (Rule) => Rule.required(),
    }),
    defineField({
      name: 'slug',
      title: 'Slug',
      type: 'slug',
      options: { source: 'title', maxLength: 96 },
      validation: (Rule) => Rule.required(),
    }),
    defineField({
      name: 'address',
      title: 'Address',
      type: 'string',
    }),
    defineField({
      name: 'price',
      title: 'Price',
      type: 'string',
    }),
    defineField({
      name: 'bedrooms',
      title: 'Bedrooms',
      type: 'number',
    }),
    defineField({
      name: 'bathrooms',
      title: 'Bathrooms',
      type: 'number',
    }),
    defineField({
      name: 'sqft',
      title: 'Square Feet',
      type: 'number',
    }),
    defineField({
      name: 'propertyType',
      title: 'Property Type',
      type: 'string',
      options: {
        list: [
          { title: 'Condominium', value: 'condominium' },
          { title: 'House', value: 'house' },
          { title: 'Townhouse', value: 'townhouse' },
          { title: 'Penthouse', value: 'penthouse' },
          { title: 'Co-op', value: 'co-op' },
        ],
      },
    }),
    defineField({
      name: 'status',
      title: 'Status',
      type: 'string',
      options: {
        list: [
          { title: 'Active', value: 'active' },
          { title: 'Pending', value: 'pending' },
          { title: 'Sold', value: 'sold' },
        ],
      },
      initialValue: 'active',
    }),
    defineField({
      name: 'featuredImage',
      title: 'Featured Image',
      type: 'image',
      options: { hotspot: true },
    }),
    defineField({
      name: 'gallery',
      title: 'Photo Gallery',
      type: 'array',
      of: [{ type: 'image', options: { hotspot: true } }],
    }),
    defineField({
      name: 'description',
      title: 'Description',
      type: 'array',
      of: [{ type: 'block' }],
    }),
    defineField({
      name: 'highlights',
      title: 'Property Highlights',
      type: 'text',
      description: 'Key features, separated by new lines',
    }),
    defineField({
      name: 'developmentDetails',
      title: 'Development Details',
      type: 'text',
    }),
    defineField({
      name: 'agent',
      title: 'Listing Agent',
      type: 'reference',
      to: [{ type: 'agent' }],
    }),
    defineField({
      name: 'order',
      title: 'Display Order',
      type: 'number',
      initialValue: 0,
    }),
  ],
  orderings: [
    {
      title: 'Display Order',
      name: 'orderAsc',
      by: [{ field: 'order', direction: 'asc' }],
    },
  ],
  preview: {
    select: { title: 'title', subtitle: 'address', media: 'featuredImage' },
  },
});
