# Pull Request Standards

## Sensible title

You need to give the PR a sensible title

## Summary in description

The description needs to contain some context of the PR. For example:

`Update the homepage sidebar copy.`

## Ticket link in description

You need to include the link to the ticket in Asana/Trello/Jira which this PR is for.

## Assigned to reviewer

All PR must be assigned to the reviewer.

## Close branch on merge

Make sure you have marked the PR to close the branch automatically once merged (checkbox when editing PR).

# Git Standards

## Bad branch name

Name your branches following our company conventions:

1. Needs to have a prefix:

- `feature/` - new feature / general tickets
- `bugfix/` - general bug fix
- `hotfix/` - bug fix which may not have been thoroughly tested before moving to prod

2. Needs to have a descriptive title. E.g. `f-35` is not acceptable. You might call it `feature/update-chomepage-copy`
instead.

## Bad commit descriptions

Give commits sensible descriptions / messages. For example, this is not acceptable:

`git commit -m "testing"`

or 

`git commit -m "update"`

You can't fix this now but don't do it again.

Commit messages should answer the question; "If I apply this commit/patch it will ________________.". For example:

`git commit -m "update logo on jobs single page"`

# Misc

## Merge Conflicts

Please fix the merge conflicts on the Pull Request
