<h1 style="display: flex; align-items: center;">
  <a style="display: inline-block;" href="https://www.ansible.com/" target="_blank">
    <picture>
      <img width="48" height="48" alt="logo" src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/ansible/ansible-original-wordmark.svg">
    </picture>
  </a>
  &nbsp;
  <p style="display: inline-block;">Ansible</p>
</h1>

> 🔔 Already created playbooks in this starter project are prepared for Enterwell's needs. Please adapt them to your hosts 
> and requirements or delete them in general

## ⚡ Run automation

```
ansible-playbook -i <inventory_file_path> -e <extra_vars> <playbook_path>
```
> 📚 **Useful links**
> - [Ansible documentation](https://docs.ansible.com/ansible/latest/index.html)

### Example
```
ansible-playbook -i /var/www/wp-starter/.ansible/inventory/hosts.yml -e "ansible_user=ec2-user mysql_user=wordpress" /var/www/wp-starter/.ansible/tasks/stage.playbook.yml
```

> 🔔 You would usually run this command in a CI/CD pipeline, before copying your code to wanted environment. Github Actions 
> and Azure Pipelines already have Ansible installed on their agents and no pre-installment is needed.

## 📖 Table of contents

- [⚡ Run automation](#-run-automation)
- [🔨 Requirements](#-requirements)
- [📘 About](#-about)
- [🏛 Folder structure](#-folder-structure)
- [🧑‍💼 Ansible in use](#-ansible-in-use)

## 🔨 Requirements

- Ansible Control Node
  - a machine on which ansible is run, a Linux distribution of sort - usually a CI/CD agent
- Ansible host(s)
  - machines on which playbook tasks are run
- [Ansible package](https://docs.ansible.com/ansible/latest/installation_guide/installation_distros.html#installing-ansible-on-ubuntu)
  - installed on the Ansible control node

## 📘 About

[Ansible](https://www.ansible.com/) is an open source tool used to automate tasks, at least in our case. In this starter project, we use it to prepare 
or local, stage, production or any other environment so that we can deploy our code to expected and tested environments. Also, 
if we migrate or want to deploy the code to other environments, we can easily do so.

## 🏛 Folder structure

```
.ansible/
├─ files/
├─ inventory/
│  ├─ hosts.yml
├─ tasks/
│  ├─ local.playbook.yml
│  ├─ stage.playbook.yml
│  ├─ ...
├─ vars/
│  ├─ default.yml
│  ├─ local.yml
│  ├─ stage.yml
│  ├─ ...
├─ README.md
```
| Folder    | File               | Description                                                                                                                                                                                                                                             |
|-----------|--------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| files     |                    | Files that we use as a template for files that need to be created on the hosts. We either copy these files AS IS or we use them as a template ([Jinja2-j2 templates](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_templating.html)) |
| inventory |                    | Where we put the Ansible inventory files                                                                                                                                                                                                                |
|           | hosts.yml          | [Inventory hosts file](https://docs.ansible.com/ansible/latest/inventory_guide/intro_inventory.html) with list of our hosts on which automated tasks are run                                                                                            |
| tasks     |                    | Where we put the Ansible playbook files                                                                                                                                                                                                                 |
|           | stage.playbook.yml | Playbook file with task that are run on stage host. We create these as much as we need and same ones can be reused on more hosts. How we write them can be [seen here](https://docs.ansible.com/ansible/latest/playbook_guide/playbooks_intro.html)     |
| vars      |                    | Where we put files with Ansible variables                                                                                                                                                                                                               |
|           | default.yml        | Ansible variables that are common for all hosts. Usually general project information like PHP and node version etc.                                                                                                                                     |
|           | stage.yml          | Ansible variables specific for stage host. Important!: sensitive information should not be written here directly, but provided and overridden through _-e_ flag when calling `ansible-playbook` command                                                 |
| README.md |                    | That's me 😉                                                                                                                                                                                                                                            |
> 🔔 You are of course welcome to organize this in any way that suits your needs

## 🧑‍💼 Ansible in use

A simple use-case is explained here:
- project is ready for deployment (to any environment)
- edit `hosts.yml` inventory file and add your host information
  - > ⚠ Don't write sensitive host information like credentials here, only general ones
- edit `default.yml` variables with general project information
- create or edit `.yml` file for your host with variables for that host
  - > ⚠ Don't write sensitive information like credentials here, only general ones
- create or edit `.yml` playbook with needed tasks
- run `ansible-playbook` [command](#-run-automation)
  - > ⚠ Your should provide the sensitive variables here with _-e_ flag