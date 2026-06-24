'use client';

import { useState, useEffect } from 'react';
import Link from 'next/link';
import styles from './Header.module.css';
import type { MenuItem, PicItem } from '@/lib/api';

interface HeaderProps {
  menuItems: MenuItem[];
  logo?: PicItem;
}

export default function Header({ menuItems, logo }: HeaderProps) {
  const [scrolled, setScrolled] = useState(false);
  const [mobileOpen, setMobileOpen] = useState(false);
  const [activeDropdown, setActiveDropdown] = useState<number | null>(null);

  useEffect(() => {
    const handleScroll = () => setScrolled(window.scrollY > 60);
    window.addEventListener('scroll', handleScroll);
    return () => window.removeEventListener('scroll', handleScroll);
  }, []);

  useEffect(() => {
    if (mobileOpen) {
      document.body.style.overflow = 'hidden';
    } else {
      document.body.style.overflow = '';
    }
    return () => { document.body.style.overflow = ''; };
  }, [mobileOpen]);

  const headerClass = [
    styles.header,
    scrolled ? styles.scrolled : '',
  ].filter(Boolean).join(' ');

  return (
    <header className={headerClass}>
      <div className={styles.inner}>
        {/* Logo */}
        <Link href="/" className={styles.logo}>
          {logo ? (
            <img src={logo.path} alt="Beaconstone Realty" className={styles.logoImg} />
          ) : (
            <span className={styles.logoText}>BEACONSTONE REALTY</span>
          )}
        </Link>

        {/* Desktop Navigation */}
        <nav className={styles.desktopNav}>
          <ul className={styles.navList}>
            {menuItems.filter(m => m.is_show).map((item) => (
              <li
                key={item.id}
                className={`${styles.navItem} ${item.children.length > 0 ? styles.hasDropdown : ''}`}
                onMouseEnter={() => setActiveDropdown(item.id)}
                onMouseLeave={() => setActiveDropdown(null)}
              >
                <Link href={item.url || '#'} className={styles.navLink}>
                  {item.title}
                </Link>
                {item.children.length > 0 && activeDropdown === item.id && (
                  <ul className={styles.dropdown}>
                    {item.children.filter(c => c.is_show).map((child) => (
                      <li key={child.id}>
                        <Link href={child.url || '#'} className={styles.dropdownLink}>
                          {child.title}
                        </Link>
                      </li>
                    ))}
                  </ul>
                )}
              </li>
            ))}
          </ul>
          <Link href="/sell-with-us" className={styles.ctaButton}>
            Sell With Us
          </Link>
        </nav>

        {/* Mobile Toggle */}
        <button
          className={`${styles.mobileToggle} ${mobileOpen ? styles.active : ''}`}
          onClick={() => setMobileOpen(!mobileOpen)}
          aria-label="Toggle navigation"
        >
          <span></span>
          <span></span>
          <span></span>
        </button>
      </div>

      {/* Mobile Navigation */}
      <div className={`${styles.mobileNav} ${mobileOpen ? styles.open : ''}`}>
        <div className={styles.mobileNavInner}>
          <ul className={styles.mobileNavList}>
            {menuItems.filter(m => m.is_show).map((item) => (
              <li key={item.id} className={styles.mobileNavItem}>
                <Link
                  href={item.url || '#'}
                  className={styles.mobileNavLink}
                  onClick={() => setMobileOpen(false)}
                >
                  {item.title}
                </Link>
                {item.children.length > 0 && (
                  <ul className={styles.mobileSubNav}>
                    {item.children.filter(c => c.is_show).map((child) => (
                      <li key={child.id}>
                        <Link
                          href={child.url || '#'}
                          className={styles.mobileSubLink}
                          onClick={() => setMobileOpen(false)}
                        >
                          {child.title}
                        </Link>
                      </li>
                    ))}
                  </ul>
                )}
              </li>
            ))}
          </ul>
          <Link
            href="/sell-with-us"
            className={styles.mobileCta}
            onClick={() => setMobileOpen(false)}
          >
            Sell With Us
          </Link>
        </div>
      </div>
    </header>
  );
}
