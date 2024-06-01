# Worpress Unityt Mirror Networking Online Hosts Management System

## Overview

This project integrates a WordPress plugin with a Unity application using Mirror Networking to manage and display the status of online hosts. The system allows Unity to update and retrieve the status of hosts dynamically, providing real-time online status data within a Unity game or application.

## Components

### 1. WordPress Plugin

The WordPress plugin consists of two parts:

#### a. Online Hosts Manager
- **Functionality**: Manages a list of online hosts from Unity Mirror Networking.
- **Features**:
  - Creates a database table to store host information.
  - Provides a REST API endpoint to update host statuses (`online` or `offline`) as they change within the Unity application.
  - Host statuses are updated via POST requests from the Unity app.

#### b. Display Online Hosts API
- **Functionality**: Provides an API endpoint that Unity can read to display online hosts.
- **Features**:
  - Offers a REST API endpoint to retrieve a list of online hosts.
  - Outputs the data in JSON format suitable for consumption by Unity or other clients.

### 2. Unity Application

- **Functionality**: Interacts with the WordPress plugin to update and retrieve the status of online hosts.
- **Features**:
  - Sends data to the WordPress site to update the status of the host when it goes online or offline.
  - Retrieves the list of online hosts from WordPress via a GET request to the provided API endpoint.
  - Uses `UnityWebRequest` to manage HTTP requests and responses.
  - Processes JSON data returned by the WordPress API to utilize within the Unity environment.

## Interaction Flow

1. **Unity to WordPress**:
   - When a host in Unity goes online or offline, Unity sends this status update to the WordPress API endpoint using a POST request.
   - The WordPress plugin updates the host status in the database based on this information.

2. **WordPress to Unity**:
   - Unity sends a GET request to the WordPress API to retrieve the current list of online hosts.
   - The WordPress plugin queries the database and returns the list of online hosts in JSON format.
   - Unity processes this data to display or use within the game/application environment.

## Usage

- **WordPress**: Activate the plugin and use the provided shortcodes or access the API endpoints directly.
- **Unity**: Implement the provided C# scripts in your Unity project to interact with the WordPress API.

This system is ideal for multiplayer games or applications using Unity and Mirror Networking that require real-time management of host statuses.

