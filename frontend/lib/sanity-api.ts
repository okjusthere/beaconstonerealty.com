/**
 * Adapter layer that maps Sanity CMS responses to the shapes
 * expected by existing page components (NewsItem-compatible).
 *
 * This avoids changing component internals - only the data source changes.
 */

import { urlFor } from '@/sanity/client';
import {
  getAllAgents,
  getAllAgentsWithBio,
  getAgentById,
  getAgentIds,
  getAllListings,
  getListingById,
  getListingIds,
} from '@/sanity/fetch';
import type { NewsItem } from './api';

// ─── Helpers ───

/**
 * Extract the legacy numeric ID from a Sanity `_id` like "agent-42" or "listing-7".
 */
function legacyIdFromSanityId(sanityId: string): number {
  const match = sanityId.match(/-(\d+)$/);
  return match ? Number(match[1]) : 0;
}

/**
 * Resolve a Sanity image object to a URL string.
 * Returns empty string if source is falsy.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function resolveImageUrl(source: any): string {
  if (!source) return '';
  try {
    return urlFor(source).url();
  } catch {
    return '';
  }
}

/**
 * Convert Sanity Portable Text blocks to an HTML string.
 * This is a minimal converter that handles the basic block types
 * produced by the seed script (plain paragraphs with spans).
 * No extra dependencies needed.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function portableTextToHtml(blocks: any): string {
  if (!blocks) return '';
  if (typeof blocks === 'string') return blocks;
  if (!Array.isArray(blocks)) return '';

  return blocks
    .map((block) => {
      if (block._type !== 'block') return '';

      const style = block.style || 'normal';
      // eslint-disable-next-line @typescript-eslint/no-explicit-any
      const text = (block.children || []).map((child: any) => {
        let t = child.text || '';
        const marks: string[] = child.marks || [];
        for (const mark of marks) {
          if (mark === 'strong') t = `<strong>${t}</strong>`;
          else if (mark === 'em') t = `<em>${t}</em>`;
        }
        return t;
      }).join('');

      if (!text) return '';

      switch (style) {
        case 'h1': return `<h1>${text}</h1>`;
        case 'h2': return `<h2>${text}</h2>`;
        case 'h3': return `<h3>${text}</h3>`;
        case 'h4': return `<h4>${text}</h4>`;
        case 'blockquote': return `<blockquote>${text}</blockquote>`;
        default: return `<p>${text}</p>`;
      }
    })
    .filter(Boolean)
    .join('\n');
}

// ─── Agent adapters ───

/**
 * Map a Sanity agent record to a NewsItem-compatible shape.
 * Used by both the broker list page and broker detail page.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function mapAgentToNewsItem(agent: Record<string, any>, includeBio = false): NewsItem {
  const id = legacyIdFromSanityId(agent._id || '');
  const slug = agent.slug?.current || '';
  const thumbnail = resolveImageUrl(agent.photo);
  const bioHtml = includeBio ? portableTextToHtml(agent.bio) : '';

  return {
    id,
    title: agent.name || '',
    url: `/brokers/${id}`,
    keywords: agent.title || '', // role like "Real Estate Advisor"
    description: '',
    thumbnail,
    content: bioHtml,
    enclosure: '',
    photo_album: [],
    add_time: 0,
    field: {
      phone: agent.phone || '',
      real_estate_broker_email: agent.email || '',
      real_estate_broker_desc: bioHtml,
      slug,
    },
  };
}

/**
 * Get all agents as NewsItem[] (for the brokers list page).
 * Includes bio so the list page can show a bio excerpt.
 */
export async function getSanityAgentList(): Promise<NewsItem[]> {
  const agents = await getAllAgentsWithBio();
  if (!agents) return [];
  return agents.map((agent) => mapAgentToNewsItem(agent, true));
}

/**
 * Get a single agent by legacy numeric ID (for the broker detail page).
 */
export async function getSanityAgentDetail(legacyId: number): Promise<NewsItem | null> {
  const agent = await getAgentById(`agent-${legacyId}`);
  if (!agent) return null;
  return mapAgentToNewsItem(agent, true);
}

/**
 * Get all agent legacy IDs for generateStaticParams.
 */
export async function getSanityAgentIds(): Promise<string[]> {
  const agents = await getAgentIds();
  if (!agents) return [];
  return agents.map((a) => String(legacyIdFromSanityId(a._id))).filter((id) => id !== '0');
}

// ─── Listing adapters ───

/**
 * Map a Sanity listing record to a NewsItem-compatible shape.
 * Used by both the properties list page and property detail page.
 */
// eslint-disable-next-line @typescript-eslint/no-explicit-any
function mapListingToNewsItem(listing: Record<string, any>, isDetail = false): NewsItem {
  const id = legacyIdFromSanityId(listing._id || '');
  const thumbnail = resolveImageUrl(listing.featuredImage);

  // Build gallery from Sanity gallery array
  const gallery: string[] = [];
  if (listing.gallery && Array.isArray(listing.gallery)) {
    for (const img of listing.gallery) {
      const url = resolveImageUrl(img);
      if (url) gallery.push(url);
    }
  }

  // Build HTML content from portable text description
  const contentHtml = isDetail ? portableTextToHtml(listing.description) : '';

  // Build field map with listing-specific metadata
  const field: Record<string, string> = {};
  if (listing.propertyType) field.development_type = listing.propertyType;
  if (listing.price) field.price = String(listing.price);
  if (listing.bedrooms) field.bedrooms = String(listing.bedrooms);
  if (listing.bathrooms) field.bathrooms = String(listing.bathrooms);
  if (listing.sqft) field.sqft = String(listing.sqft);
  if (listing.highlights) field.house_introduction = listing.highlights;
  if (listing.developmentDetails) field.development_details = listing.developmentDetails;

  // Store agent reference ID so the detail page can look up the agent
  if (listing.agent?._id) {
    const agentLegacyId = legacyIdFromSanityId(listing.agent._id);
    field.real_estate_agent_id = String(agentLegacyId);
  }

  // Map price into description (used as "Starting Price" on list page)
  // Map address into the field map so detail page can access it
  if (listing.address) field.address = listing.address;
  if (listing.totalResidences) field.total_residences = String(listing.totalResidences);

  return {
    id,
    title: listing.title || '',
    url: `/properties/${id}`,
    keywords: listing.propertyType || '',
    description: listing.price || '',
    thumbnail,
    content: contentHtml,
    enclosure: '',
    photo_album: gallery,
    add_time: 0,
    field,
  };
}

/**
 * Get all listings as NewsItem[] (for the properties list page).
 */
export async function getSanityListingList(): Promise<NewsItem[]> {
  const listings = await getAllListings();
  if (!listings) return [];
  return listings.map((listing) => mapListingToNewsItem(listing));
}

/**
 * Get a single listing by legacy numeric ID (for the property detail page).
 * Also resolves the associated agent into a NewsItem for the sidebar.
 */
export async function getSanityListingDetail(legacyId: number): Promise<{
  listing: NewsItem;
  agent: NewsItem | null;
} | null> {
  const raw = await getListingById(`listing-${legacyId}`);
  if (!raw) return null;

  const listing = mapListingToNewsItem(raw, true);

  // Map the embedded agent reference if present
  let agent: NewsItem | null = null;
  if (raw.agent && typeof raw.agent === 'object') {
    // eslint-disable-next-line @typescript-eslint/no-explicit-any
    const agentData = raw.agent as Record<string, any>;
    agent = mapAgentToNewsItem(agentData, false);
  }

  return { listing, agent };
}

/**
 * Get all listing legacy IDs for generateStaticParams.
 */
export async function getSanityListingIds(): Promise<string[]> {
  const listings = await getListingIds();
  if (!listings) return [];
  return listings.map((l) => String(legacyIdFromSanityId(l._id))).filter((id) => id !== '0');
}

/**
 * Get all agents as NewsItem[] (needed by property detail page to find an agent).
 */
export async function getSanityAllAgents(): Promise<NewsItem[]> {
  const agents = await getAllAgents();
  if (!agents) return [];
  return agents.map((agent) => mapAgentToNewsItem(agent, false));
}
