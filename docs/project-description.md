# Village show project
## 1. Project Overview
Create a MVP for a village show
## 2. Key Features
Main entities are:
- ShowSection, examples Vegetables, Baking, Art
- ShowClass, each of which belongs to one ShowSection
- Exhibitor, who may be an adult or junior, resident or non-resident in the village. Adults pay for each of their entries up to 10, after which additional entries are free. Juniors may may unlimited entries for free.
- Judge, who is judges one or more ShowSections
- Result, awarding points to an Exhibitor in a ShowClass
    - 1st(3 points)
    - 2nd(2 points)
    - 3rd(1 point)
    - highly commended (0 points) 
- Trophy, awarded to the Exhibitor with the most points in a subset of ShowClasses. Each ShowClass may count towards no, one, or several Trophies. 
## 3. Technical Requirements
- Laravel 13
- Tailwind 4
- MySQL
- Livewire but create new pages as Laravel Controllers and not as Livewire components, unless specifically stated otherwise or unless dynamic Livewire behavior is required on that page.
