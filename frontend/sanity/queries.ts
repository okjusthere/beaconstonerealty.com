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
    totalResidences,
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
    totalResidences,
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
    totalResidences,
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

// ─── News ───
export const allNewsArticlesQuery = groq`
  *[_type == "newsArticle"] | order(publishedAt desc) {
    _id,
    legacyId,
    title,
    slug,
    excerpt,
    coverImage,
    coverImageAlt,
    publishedAt,
    featured,
    seoTitle,
    seoDescription,
    authorAgent-> {
      _id,
      name,
      slug,
      title,
      photo
    }
  }
`;

export const featuredNewsArticlesQuery = groq`
  *[_type == "newsArticle" && featured == true] | order(publishedAt desc)[0...3] {
    _id,
    legacyId,
    title,
    slug,
    excerpt,
    coverImage,
    coverImageAlt,
    publishedAt,
    featured,
    seoTitle,
    seoDescription,
    authorAgent-> {
      _id,
      name,
      slug,
      title,
      photo
    }
  }
`;

export const newsArticleByIdentifierQuery = groq`
  *[
    _type == "newsArticle" &&
    (
      slug.current == $slug ||
      legacyId == $legacyId
    )
  ][0] {
    _id,
    legacyId,
    title,
    slug,
    excerpt,
    coverImage,
    coverImageAlt,
    publishedAt,
    featured,
    seoTitle,
    seoDescription,
    body,
    authorAgent-> {
      _id,
      name,
      slug,
      title,
      photo
    }
  }
`;

export const newsArticleRouteParamsQuery = groq`
  *[_type == "newsArticle" && defined(slug.current)] | order(publishedAt desc) {
    "slug": slug.current,
    "legacyId": string(legacyId)
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
