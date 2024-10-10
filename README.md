# Scheduler Bundle

A Symfony bundle to manage, schedule and execute Symfony commands via cron. This bundle provides an easy way to handle tasks that need to run on a periodic basis, whether for background processing or automated maintenance tasks.

## Features
- Manage scheduled tasks with cron
- Automatic retries with exponential backoff
- Customizable task execution intervals
- Integration with Symfony's console, validation, and cache components

## Installation

To install this bundle, run the following command:

```bash
composer require mikamatto/scheduler-bundle