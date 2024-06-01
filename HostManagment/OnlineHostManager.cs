using UnityEngine;
using UnityEngine.Networking;
using Mirror;

public class OnlineHostManager : NetworkManager
{
    private string apiUrl = "http://yourwebsite.com/wp-json/ohm/v1/update/";

    public override void OnStartServer()
    {
        base.OnStartServer();
        StartCoroutine(UpdateHostStatus("online"));
    }

    public override void OnStopServer()
    {
        base.OnStopServer();
        StartCoroutine(UpdateHostStatus("offline"));
    }

    IEnumerator UpdateHostStatus(string status)
    {
        WWWForm form = new WWWForm();
        form.AddField("host_name", System.Environment.MachineName);
        form.AddField("status", status);

        using (UnityWebRequest www = UnityWebRequest.Post(apiUrl, form))
        {
            yield return www.SendWebRequest();

            if (www.result != UnityWebRequest.Result.Success)
            {
                Debug.LogError("Error updating host status: " + www.error);
            }
            else
            {
                Debug.Log("Host status updated successfully");
            }
        }
    }
}
