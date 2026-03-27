import groq from 'groq';

// ─── Site Settings ───
export const siteSettingsQuery = groq`
  *[_type == "siteSettings"][0] {
    companyName,
    logo,
    address,
    phone,
    email,
    heroVideoPlaybackId,
    socialLinks
  }
`;

// ─── Agents ───
export const allAgentsQuery = groq`
  *[_type == "agent"] | order(order asc) {
    _id,
    name,
    slug,
    title,
    photo,
    phone,
    email,
    region,
    order
  }
`;

export const agentBySlugQuery = groq`
  *[_type == "agent" && slug.current == $slug][0] {
    _id,
    name,
    slug,
    title,
    photo,
    phone,
    email,
    region,
    bio,
    order
  }
`;

export const agentSlugsQuery = groq`
  *[_type == "agent" && defined(slug.current)] {
    "slug": slug.current
  }
`;

export const agentByIdQuery = groq`
  *[_type == "agent" && _id == $id][0] {
    _id,
    name,
    slug,
    title,
    photo,
    phone,
    email,
    region,
    bio,
    order
  }
`;

export const agentIdsQuery = groq`
  *[_type == "agent"] | order(order asc) {
    _id
  }
`;

export const allAgentsWithBioQuery = groq`
  *[_type == "agent"] | order(order asc) {
    _id,
    name,
    slug,
    title,
    photo,
    phone,
    email,
    region,
    bio,
    order
  }
`;

// ─── Listings ───
export const allListingsQuery = groq`
  *[_type == "listing" && status != "sold"] | order(order asc) {
    _id,
    title,
    slug,
    address,
    price,
    bedrooms,
    bathrooms,
    sqft,
    propertyType,
    status,
    featuredImage,
    order,
    "agentName": agent->name
  }
`;

export const listingBySlugQuery = groq`
  *[_type == "listing" && slug.current == $slug][0] {
    _id,
    title,
    slug,
    address,
    price,
    bedrooms,
    bathrooms,
    sqft,
    propertyType,
    status,
    featuredImage,
    gallery,
    description,
    highlights,
    developmentDetails,
    agent-> {
      _id,
      name,
      slug,
      title,
      photo,
      phone,
      email
    },
    order
  }
`;

export const listingSlugsQuery = groq`
  *[_type == "listing" && defined(slug.current)] {
    "slug": slug.current
  }
`;

export const listingByIdQuery = groq`
  *[_type == "listing" && _id == $id][0] {
    _id,
    title,
    slug,
    address,
    price,
    bedrooms,
    bathrooms,
    sqft,
    propertyType,
    status,
    featuredImage,
    gallery,
    description,
    highlights,
    developmentDetails,
    agent-> {
      _id,
      name,
      slug,
      title,
      photo,
      phone,
      email
    },
    order
  }
`;

export const listingIdsQuery = groq`
  *[_type == "listing" && status != "sold"] | order(order asc) {
    _id
  }
`;

// ─── Pages ───
export const pageBySlugQuery = groq`
  *[_type == "page" && slug.current == $slug][0] {
    _id,
    title,
    slug,
    heroTitle,
    heroSubtitle,
    heroImage,
    sections
  }
`;
