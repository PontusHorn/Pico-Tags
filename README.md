# Pico Tags

A plugin for the flat file CMS [Pico](https://github.com/picocms/Pico). Using this plugin, you can use the `Tags` and
`Filter` headers in the page meta block in order to modify the `pages` array for pages of your choice. This creates the
possibility to feature index pages which show only posts of a certain type.

The `Tags` header accepts a comma-separated list of tags that apply to the current page.

The `Filter` header also accepts a comma-separated list of tags, but instead specifies which pages end up in the `pages`
array. A page with no `Filter` header will have an unfiltered list of pages, whereas a page that specifies the header
`Filter: foo, bar` will receive in its `pages` array only pages having at least one of those two tags.

## Installation

Copy the file `PicoTags.php` to the `plugins` subdirectory of your Pico installation directory. That's it!

## Usage

To assign tags to a page, specify the `Tags` header inside the meta header block at the top of your file, e.g.:

### In blog/my-first-blog-post.md
```
---
Title: My first blog post
Date: 2015-09-16 13:37:00
Description: A thrilling, must-read blog post about pancakes.
Tags: blog, pancakes
Template: blog-post
---
```

To only show pages with certain tags on another page, use the `Filter` header, e.g.:

### In blog/index.md
```
---
Title: Blog
Filter: blog
Template: blog-list
---

These are all my blog posts:
```

Actually looping through the filtered list of pages (in the above case, pages tagged `blog`) to display them would be
done in the template file, e.g.:

### In themes/default/blog-list.html
```twig
{{ content }}
{% for page in pages if page.title %}
    <article>
        <h2><a href="{{ page.url }}">{{ page.title }}</a></h2>
        <p>{{ page.description }}</p>
    </article>
{% endfor %}
```
